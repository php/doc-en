#!/usr/bin/env bash

# Apache: listen on 8080 so non-root tooling can interact. The web root is
# linked in by .devcontainer/configure-apache.sh after the first `make` build.
sed -i 's/Listen 80$//' /etc/apache2/ports.conf
sed -i 's/<VirtualHost \*:80>/ServerName 127.0.0.1\n<VirtualHost \*:8080>/' /etc/apache2/sites-enabled/000-default.conf

cat <<'EOF'

  Devcontainer ready.

  Build the docs:    make                (chunked HTML, served at :8080)
                     make php            (PHP web format)
  View them:         http://localhost:8080

EOF
