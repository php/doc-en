#!/usr/bin/env bash

sed -i 's/Listen 80$//' /etc/apache2/ports.conf
sed -i 's/<VirtualHost \*:80>/ServerName 127.0.0.1\n<VirtualHost \*:8080>/' /etc/apache2/sites-enabled/000-default.conf
