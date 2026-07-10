#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

[[ -f build/app.js ]] || { echo "Missing build/app.js" >&2; exit 1; }
[[ -f build/style.css ]] || { echo "Missing build/style.css" >&2; exit 1; }

TARGET="$ROOT/.dev/theme/scam-dev"
rm -rf "$TARGET"
mkdir -p "$TARGET/assets/css" "$TARGET/assets/js"

find . -maxdepth 1 -type f -name '*.php' -exec cp '{}' "$TARGET/" ';'
cp style.css theme.json LICENSE "$TARGET/"
[[ -f screenshot.png ]] && cp screenshot.png "$TARGET/"

cp -R inc template-parts "$TARGET/"
cp assets/css/highlight.css assets/css/highlight-dark.css "$TARGET/assets/css/"
cp assets/js/theme-init.js "$TARGET/assets/js/"
cp build/*.css "$TARGET/assets/css/"
cp build/app.js "$TARGET/assets/js/"
[[ -f build/app.asset.php ]] && cp build/app.asset.php "$TARGET/assets/js/"

find "$TARGET" -type d -exec chmod 0755 '{}' ';'
find "$TARGET" -type f -exec chmod 0644 '{}' ';'

echo "Development theme synchronized to $TARGET"
