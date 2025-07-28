#!/usr/bin/env bash

if [ -z "$1" ] || [ ! -d "$1" ]; then
  echo "Usage: $0 <path-to-web-root-directory>"
  exit
fi

sudo rm -rf /var/www/html
sudo chmod a+x "$1"
sudo ln -s "$1" /var/www/html && \
  echo "Apache web root directory set to $1"
if ! pgrep -x "apache2" > /dev/null; then
  apache2ctl start
fi
