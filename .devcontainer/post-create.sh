#!/usr/bin/env bash

set -e

# Apache: listen on 8080 so non-root tooling can interact.
sed -i 's/Listen 80$//' /etc/apache2/ports.conf
sed -i 's/<VirtualHost \*:80>/ServerName 127.0.0.1\n<VirtualHost \*:8080>/' /etc/apache2/sites-enabled/000-default.conf

WORKSPACE="$(cd "$(dirname "$0")/.." && pwd)"
PARENT="$(dirname "$WORKSPACE")"
OWNER="$(stat -c '%U' "$WORKSPACE")"

# Clone doc-base and phd as siblings of doc-en.
[ -d "$PARENT/doc-base" ] || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/doc-base.git
[ -d "$PARENT/phd" ]      || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/phd.git

# Serve the rendered output (not the XML sources) via Apache.
mkdir -p "$WORKSPACE/output/php-chunked-xhtml"
chown -R "$OWNER:$OWNER" "$WORKSPACE/output"
chmod a+x "$WORKSPACE"
rm -rf /var/www/html
ln -s "$WORKSPACE/output/php-chunked-xhtml" /var/www/html

cat <<'EOF'

  Devcontainer ready.

  Build the docs:    .devcontainer/build.sh
  View them:         http://localhost:8080            (forwarded port 8080)

EOF
