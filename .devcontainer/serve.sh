#!/usr/bin/env bash

if [ -z "$1" ] || [ ! -d "$1" ]; then
  echo "Usage: $0 <path-to-web-root-directory>"
  exit 1
fi

sudo rm -rf /var/www/html
sudo chmod a+x "$1"
sudo ln -s "$1" /var/www/html && \
  echo "Now serving $1 at http://localhost:8080"
