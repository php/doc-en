#!/usr/bin/env bash

set -e

. /etc/os-release
curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/sury-php.gpg
echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $VERSION_CODENAME main" \
  > /etc/apt/sources.list.d/sury-php.list
apt-get update
apt-get install -y --no-install-recommends php8.4-cli

mkdir -p /var/www/html
cat >/var/www/html/index.html <<'HTML'
<!doctype html>
<title>PHP Docs devcontainer</title>
<p>No build yet. Run <code>make</code> or <code>make php</code>.</p>
HTML

cat <<'EOF'

  Devcontainer ready.

  Build the docs:    make                (chunked HTML, served at :8080)
                     make php            (PHP web format)
  View them:         http://localhost:8080

EOF
