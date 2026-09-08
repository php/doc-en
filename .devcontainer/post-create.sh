#!/usr/bin/env bash

set -e

WORKSPACE="$(cd "$(dirname "$0")/.." && pwd)"
PARENT="$(dirname "$WORKSPACE")"
OWNER="$(stat -c '%U' "$WORKSPACE")"

# Clone doc-base, phd, and web-php as siblings of doc-en.
[ -d "$PARENT/doc-base" ] || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/doc-base.git
[ -d "$PARENT/phd" ]      || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/phd.git
[ -d "$PARENT/web-php" ]  || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/web-php.git

# doc-base's configure.php looks for the language source as a sibling directory
[ -e "$PARENT/en" ] || sudo -u "$OWNER" ln -s "$WORKSPACE" "$PARENT/en"

# Xdebug degrades performance and is not needed for the build, so disable it by default.
rm -f /usr/local/etc/php/conf.d/xdebug.ini

# Pre-create the served directories
sudo -u "$OWNER" mkdir -p "$WORKSPACE/output/php-chunked-xhtml" "$WORKSPACE/output/php-web"
sudo -u "$OWNER" rm -rf "$PARENT/web-php/manual/en"
sudo -u "$OWNER" ln -s "$WORKSPACE/output/php-web" "$PARENT/web-php/manual/en"

cat <<'EOF'

  Devcontainer ready.

  Build & serve:     F5 > "Build XHTML & serve"   (or "Build PHP web & serve")

  View them:         http://localhost:8080

EOF
