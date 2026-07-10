#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
	echo "Create .env from .env.example before deployment." >&2
	exit 1
fi

set -a
# shellcheck disable=SC1091
source ./.env
set +a

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${SITE_DOMAIN:?SITE_DOMAIN is required}"
: "${UPDATE_SIGNING_PRIVATE_KEY_FILE:?UPDATE_SIGNING_PRIVATE_KEY_FILE is required}"

DEPLOY_PORT="${DEPLOY_PORT:-22}"
WORDPRESS_ROOT="${WORDPRESS_ROOT:-/var/www/html}"
REMOTE_RELEASE_ROOT="${REMOTE_RELEASE_ROOT:-/var/www/releases/scam-dev}"
DEPLOY_OWNER="${DEPLOY_OWNER:-$DEPLOY_USER}"
DEPLOY_GROUP="${DEPLOY_GROUP:-www-data}"

validate_absolute_path() {
	local value="$1"

	[[ "$value" =~ ^/[A-Za-z0-9._/-]+$ ]] \
		&& [[ "$value" != "/" ]] \
		&& [[ "$value" != *"//"* ]] \
		&& [[ "$value" != *"/../"* ]] \
		&& [[ "$value" != */.. ]] \
		&& [[ "$value" != *"/./"* ]] \
		&& [[ "$value" != */. ]]
}

[[ "$DEPLOY_PORT" =~ ^[0-9]+$ ]] \
	&& (( DEPLOY_PORT >= 1 && DEPLOY_PORT <= 65535 )) \
	|| { echo "Invalid DEPLOY_PORT" >&2; exit 1; }
[[ "$DEPLOY_HOST" =~ ^[A-Za-z0-9._-]+$ ]] \
	|| { echo "Invalid DEPLOY_HOST" >&2; exit 1; }
[[ "$DEPLOY_USER" =~ ^[A-Za-z_][A-Za-z0-9._-]*$ ]] \
	|| { echo "Invalid DEPLOY_USER" >&2; exit 1; }
[[ "$SITE_DOMAIN" =~ ^[A-Za-z0-9.-]+$ ]] \
	&& [[ "$SITE_DOMAIN" != .* ]] \
	&& [[ "$SITE_DOMAIN" != *. ]] \
	|| { echo "Invalid SITE_DOMAIN" >&2; exit 1; }
[[ "$DEPLOY_OWNER" =~ ^[A-Za-z_][A-Za-z0-9_-]*[$]?$ ]] \
	|| { echo "Invalid DEPLOY_OWNER" >&2; exit 1; }
[[ "$DEPLOY_GROUP" =~ ^[A-Za-z_][A-Za-z0-9_-]*[$]?$ ]] \
	|| { echo "Invalid DEPLOY_GROUP" >&2; exit 1; }
validate_absolute_path "$WORDPRESS_ROOT" \
	|| { echo "Invalid WORDPRESS_ROOT" >&2; exit 1; }
validate_absolute_path "$REMOTE_RELEASE_ROOT" \
	|| { echo "Invalid REMOTE_RELEASE_ROOT" >&2; exit 1; }
[[ -r "$UPDATE_SIGNING_PRIVATE_KEY_FILE" ]] \
	|| { echo "Signing key is unreadable" >&2; exit 1; }

VERSION="$(awk -F': ' '/^Version:/ { gsub(/[[:space:]]/, "", $2); print $2; exit }' style.css)"
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+([+-][0-9A-Za-z.-]+)?$ ]]; then
	echo "Invalid theme version: $VERSION" >&2
	exit 1
fi

THEME_ZIP="$ROOT/scam-dev-$VERSION.zip"
[[ -f "$THEME_ZIP" ]] || { echo "Run make package first" >&2; exit 1; }

PLUGIN_NAMES=(
	scam-dev-donate-widget
	scam-dev-gallery
	scam-dev-matrix
	scam-dev-seo
	scam-dev-svg
	scam-dev-vk-import
)

for plugin_name in "${PLUGIN_NAMES[@]}"; do
	[[ -f "$ROOT/$plugin_name.zip" ]] || {
		echo "Missing plugin package: $plugin_name.zip" >&2
		exit 1
	}
done

HASH="$(sha256sum "$THEME_ZIP" | awk '{print $1}')"
RELEASE_ID="${VERSION}-$(date -u +%Y%m%dT%H%M%SZ)"
DOWNLOAD_URL="https://${SITE_DOMAIN}/downloads/scam-dev-${VERSION}.zip"

WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

PAYLOAD="$WORK/update-payload.json"
MANIFEST="$WORK/theme-update.json"

python3 - "$PAYLOAD" "$VERSION" "$DOWNLOAD_URL" "$HASH" <<'PY'
import json
import sys

path, version, download_url, sha256 = sys.argv[1:]
payload = {
    "download_url": download_url,
    "requires": "6.5",
    "requires_php": "8.0",
    "sha256": sha256,
    "url": "https://github.com/scamII/scam-dev",
    "version": version,
}
with open(path, "w", encoding="utf-8", newline="\n") as handle:
    json.dump(payload, handle, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
    handle.write("\n")
PY

php scripts/sign-update-manifest.php \
	"$PAYLOAD" \
	"$UPDATE_SIGNING_PRIVATE_KEY_FILE" \
	"$MANIFEST"

TARGET="${DEPLOY_USER}@${DEPLOY_HOST}"
REMOTE_INCOMING="${REMOTE_RELEASE_ROOT}/incoming/${RELEASE_ID}"

ssh -p "$DEPLOY_PORT" "$TARGET" \
	"mkdir -p $(printf '%q' "$REMOTE_INCOMING")"

scp -P "$DEPLOY_PORT" "$THEME_ZIP" "$TARGET:$REMOTE_INCOMING/"
scp -P "$DEPLOY_PORT" "$MANIFEST" "$TARGET:$REMOTE_INCOMING/"

for plugin_name in "${PLUGIN_NAMES[@]}"; do
	scp -P "$DEPLOY_PORT" \
		"$ROOT/$plugin_name.zip" \
		"$TARGET:$REMOTE_INCOMING/"
done

printf -v REMOTE_ARGS '%q ' \
	"$RELEASE_ID" \
	"$VERSION" \
	"$REMOTE_RELEASE_ROOT" \
	"$WORDPRESS_ROOT" \
	"$DEPLOY_OWNER" \
	"$DEPLOY_GROUP"

rollback_remote() {
	ssh -p "$DEPLOY_PORT" "$TARGET" \
		"bash -s -- $REMOTE_ARGS" <<'REMOTE'
set -euo pipefail

RELEASE_ID="$1"
VERSION="$2"
RELEASE_ROOT="$3"
WP_ROOT="$4"
OWNER="$5"
GROUP="$6"
unset VERSION OWNER GROUP

STATE="$RELEASE_ROOT/state/$RELEASE_ID"
INCOMING="$RELEASE_ROOT/incoming/$RELEASE_ID"
THEME_TARGET="$WP_ROOT/wp-content/themes/scam-dev"

restore_target() {
	local target="$1"
	local state_base="$2"

	if [[ -s "$state_base.previous" ]]; then
		local previous
		previous="$(cat "$state_base.previous")"
		ln -sfn "$previous" "$target.next"
		mv -Tf "$target.next" "$target"
	elif [[ -e "$state_base.absent" ]]; then
		rm -f "$target"
	fi
}

restore_target "$THEME_TARGET" "$STATE/theme"

if [[ -f "$STATE/installed-plugins" ]]; then
	while IFS= read -r name; do
		[[ "$name" =~ ^scam-dev-[a-z0-9-]+$ ]] || continue
		restore_target \
			"$WP_ROOT/wp-content/plugins/$name" \
			"$STATE/plugin-$name"
		rm -rf "$RELEASE_ROOT/releases/plugins/$name/$RELEASE_ID"
	done < "$STATE/installed-plugins"
fi

rm -rf "$RELEASE_ROOT/releases/theme/$RELEASE_ID"
rm -rf "$INCOMING"
printf '%s\n' "rolled-back" > "$STATE/result"
REMOTE
}

# Phase one: validate archives, create immutable releases and atomically switch
# the active theme/plugin symlinks. The public update channel is not changed yet.
if ! ssh -p "$DEPLOY_PORT" "$TARGET" \
	"bash -s -- $REMOTE_ARGS" <<'REMOTE'
set -euo pipefail
shopt -s nullglob

RELEASE_ID="$1"
VERSION="$2"
RELEASE_ROOT="$3"
WP_ROOT="$4"
OWNER="$5"
GROUP="$6"

INCOMING="$RELEASE_ROOT/incoming/$RELEASE_ID"
STATE="$RELEASE_ROOT/state/$RELEASE_ID"
THEME_RELEASE="$RELEASE_ROOT/releases/theme/$RELEASE_ID"
THEME_TARGET="$WP_ROOT/wp-content/themes/scam-dev"

rm -rf "$STATE" "$THEME_RELEASE"
mkdir -p \
	"$STATE" \
	"$THEME_RELEASE" \
	"$WP_ROOT/wp-content/themes" \
	"$WP_ROOT/wp-content/plugins"
: > "$STATE/installed-plugins"

record_previous() {
	local target="$1"
	local state_base="$2"
	local legacy="$3"

	if [[ -L "$target" ]]; then
		local previous
		previous="$(readlink -f "$target" || true)"
		if [[ -n "$previous" ]]; then
			printf '%s\n' "$previous" > "$state_base.previous"
		else
			rm -f "$target"
			touch "$state_base.absent"
		fi
	elif [[ -e "$target" ]]; then
		mkdir -p "$(dirname "$legacy")"
		mv "$target" "$legacy"
		printf '%s\n' "$legacy" > "$state_base.previous"
	else
		touch "$state_base.absent"
	fi
}

unzip -q "$INCOMING/scam-dev-$VERSION.zip" -d "$THEME_RELEASE"
[[ -f "$THEME_RELEASE/scam-dev/style.css" ]]
[[ -f "$THEME_RELEASE/scam-dev/index.php" ]]

while IFS= read -r -d '' file; do
	php -l "$file" >/dev/null
done < <(find "$THEME_RELEASE/scam-dev" -type f -name '*.php' -print0)

find "$THEME_RELEASE/scam-dev" -type d -exec chmod 0755 '{}' ';'
find "$THEME_RELEASE/scam-dev" -type f -exec chmod 0644 '{}' ';'
chown -R "$OWNER:$GROUP" "$THEME_RELEASE/scam-dev"

record_previous \
	"$THEME_TARGET" \
	"$STATE/theme" \
	"$RELEASE_ROOT/legacy/theme-$RELEASE_ID"
ln -sfn "$THEME_RELEASE/scam-dev" "$THEME_TARGET.next"
mv -Tf "$THEME_TARGET.next" "$THEME_TARGET"

for archive in "$INCOMING"/scam-dev-*.zip; do
	name="$(basename "$archive" .zip)"
	[[ "$name" == "scam-dev-$VERSION" ]] && continue
	[[ "$name" =~ ^scam-dev-[a-z0-9-]+$ ]] || {
		echo "Invalid plugin package name: $name" >&2
		exit 1
	}

	plugin_release="$RELEASE_ROOT/releases/plugins/$name/$RELEASE_ID"
	plugin_target="$WP_ROOT/wp-content/plugins/$name"
	rm -rf "$plugin_release"
	mkdir -p "$plugin_release"
	unzip -q "$archive" -d "$plugin_release"

	main_file="$plugin_release/$name.php"
	[[ -f "$main_file" ]] || {
		echo "Missing plugin entry file: $main_file" >&2
		exit 1
	}

	while IFS= read -r -d '' file; do
		php -l "$file" >/dev/null
	done < <(find "$plugin_release" -type f -name '*.php' -print0)

	find "$plugin_release" -type d -exec chmod 0755 '{}' ';'
	find "$plugin_release" -type f -exec chmod 0644 '{}' ';'
	chown -R "$OWNER:$GROUP" "$plugin_release"

	record_previous \
		"$plugin_target" \
		"$STATE/plugin-$name" \
		"$RELEASE_ROOT/legacy/plugin-$name-$RELEASE_ID"
	ln -sfn "$plugin_release" "$plugin_target.next"
	mv -Tf "$plugin_target.next" "$plugin_target"
	printf '%s\n' "$name" >> "$STATE/installed-plugins"
done

printf '%s\n' "switched" > "$STATE/result"
REMOTE
then
	echo "Release switch failed; rolling back partial changes." >&2
	rollback_remote
	exit 1
fi

if ! curl --fail --silent --show-error \
	--location --max-redirs 3 \
	--max-time 20 \
	"https://${SITE_DOMAIN}/?rest_route=/" >/dev/null; then
	echo "Health check failed; rolling back symlinks." >&2
	rollback_remote
	exit 1
fi

# Phase two: publish the signed update archive and manifest only after the new
# release has passed its public health check.
if ! ssh -p "$DEPLOY_PORT" "$TARGET" \
	"bash -s -- $REMOTE_ARGS" <<'REMOTE'
set -euo pipefail

RELEASE_ID="$1"
VERSION="$2"
RELEASE_ROOT="$3"
WP_ROOT="$4"
OWNER="$5"
GROUP="$6"

INCOMING="$RELEASE_ROOT/incoming/$RELEASE_ID"
STATE="$RELEASE_ROOT/state/$RELEASE_ID"
DOWNLOAD_DIR="$WP_ROOT/downloads"

[[ -f "$INCOMING/scam-dev-$VERSION.zip" ]]
[[ -f "$INCOMING/theme-update.json" ]]
mkdir -p "$DOWNLOAD_DIR"

install -o "$OWNER" -g "$GROUP" -m 0644 \
	"$INCOMING/scam-dev-$VERSION.zip" \
	"$DOWNLOAD_DIR/scam-dev-$VERSION.zip.next"
mv -Tf \
	"$DOWNLOAD_DIR/scam-dev-$VERSION.zip.next" \
	"$DOWNLOAD_DIR/scam-dev-$VERSION.zip"

install -o "$OWNER" -g "$GROUP" -m 0644 \
	"$INCOMING/theme-update.json" \
	"$WP_ROOT/theme-update.json.next"
mv -Tf "$WP_ROOT/theme-update.json.next" "$WP_ROOT/theme-update.json"

rm -rf "$INCOMING"
printf '%s\n' "deployed" > "$STATE/result"
REMOTE
then
	echo "Publishing the update channel failed; rolling back." >&2
	rollback_remote
	exit 1
fi

echo "Deployment completed: $RELEASE_ID"
echo "Signed update manifest published after a successful health check."
