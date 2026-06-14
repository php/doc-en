#!/usr/bin/env bash

set -e

if [ -n "$1" ]; then
  if [ ! -d "$1" ]; then
    echo "Usage: $0 [<path-to-web-root-directory>]"
    exit 1
  fi
  sudo rm -rf /var/www/html
  sudo chmod a+x "$1"
  sudo ln -s "$1" /var/www/html
  echo "Web root set to $1"
fi

if pgrep -f 'php -S 0.0.0.0:8080' >/dev/null; then
  echo "Server already running at http://localhost:8080"
  exit 0
fi

echo "Starting server at http://localhost:8080"
exec php -S 0.0.0.0:8080 -t /var/www/html
