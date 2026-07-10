#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

VERSION="$(awk -F': ' '/^Version:/ { gsub(/[[:space:]]/, "", $2); print $2; exit }' style.css)"
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+([+-][0-9A-Za-z.-]+)?$ ]]; then
	echo "Invalid theme version: $VERSION" >&2
	exit 1
fi

[[ -f build/app.js ]] || { echo "Missing build/app.js" >&2; exit 1; }
[[ -f build/style.css ]] || { echo "Missing build/style.css" >&2; exit 1; }

DIST="$(mktemp -d)"
trap 'rm -rf "$DIST"' EXIT

THEME_DIR="$DIST/scam-dev"
mkdir -p "$THEME_DIR/assets/css" "$THEME_DIR/assets/js"

find . -maxdepth 1 -type f -name '*.php' -exec cp '{}' "$THEME_DIR/" ';'
cp style.css theme.json LICENSE "$THEME_DIR/"
[[ -f screenshot.png ]] && cp screenshot.png "$THEME_DIR/"

cp -R inc template-parts "$THEME_DIR/"
cp assets/css/highlight.css assets/css/highlight-dark.css "$THEME_DIR/assets/css/"
cp assets/js/theme-init.js "$THEME_DIR/assets/js/"
cp build/*.css "$THEME_DIR/assets/css/"
cp build/app.js "$THEME_DIR/assets/js/"
[[ -f build/app.asset.php ]] && cp build/app.asset.php "$THEME_DIR/assets/js/"

find "$THEME_DIR" -type d -exec chmod 0755 '{}' ';'
find "$THEME_DIR" -type f -exec chmod 0644 '{}' ';'

THEME_ZIP="$ROOT/scam-dev-$VERSION.zip"
rm -f "$THEME_ZIP"
(
	cd "$DIST"
	zip -qr "$THEME_ZIP" scam-dev
)

PLUGIN_ZIPS=()
for plugin_dir in plugins/*/; do
	plugin_name="$(basename "$plugin_dir")"
	plugin_zip="$ROOT/$plugin_name.zip"
	rm -f "$plugin_zip" "$plugin_zip.sha256"
	(
		cd "$plugin_dir"
		zip -qr "$plugin_zip" . \
			-x '*.DS_Store' \
			-x '*.log' \
			-x 'tests/*'
	)
	sha256sum "$plugin_zip" > "$plugin_zip.sha256"
	PLUGIN_ZIPS+=( "$plugin_zip" )
done

SMOKE="$(mktemp -d)"
trap 'rm -rf "$DIST" "$SMOKE"' EXIT
unzip -q "$THEME_ZIP" -d "$SMOKE"

[[ -f "$SMOKE/scam-dev/style.css" ]]
[[ -f "$SMOKE/scam-dev/index.php" ]]
[[ -f "$SMOKE/scam-dev/assets/js/app.js" ]]
[[ ! -e "$SMOKE/scam-dev/.git" ]]
[[ ! -e "$SMOKE/scam-dev/.env" ]]
[[ ! -e "$SMOKE/scam-dev/package.json" ]]
[[ ! -e "$SMOKE/scam-dev/scam-dev-full-audit-report-ru-utf8-bom.md" ]]

while IFS= read -r -d '' php_file; do
	php -l "$php_file" >/dev/null
done < <(find "$SMOKE" -type f -name '*.php' -print0)

for plugin_zip in "${PLUGIN_ZIPS[@]}"; do
	plugin_name="$(basename "$plugin_zip" .zip)"
	plugin_smoke="$SMOKE/plugins/$plugin_name"
	mkdir -p "$plugin_smoke"
	unzip -q "$plugin_zip" -d "$plugin_smoke"
	[[ -f "$plugin_smoke/$plugin_name.php" ]]
	while IFS= read -r -d '' php_file; do
		php -l "$php_file" >/dev/null
	done < <(find "$plugin_smoke" -type f -name '*.php' -print0)
done

sha256sum "$THEME_ZIP" > "$THEME_ZIP.sha256"

echo "Theme package: $THEME_ZIP"
echo "Plugin packages:"
ls -1 "$ROOT"/scam-dev-*.zip | grep -v "scam-dev-$VERSION.zip" || true
