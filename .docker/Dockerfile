# Dockerfile : build et serveur de la documentation PHP officielle
FROM php:8.2-cli-alpine

LABEL maintainer="PHP Documentation Team <doc@php.net>"
LABEL description="Image pour construire et servir la documentation PHP (format php-chunked-xhtml)"

ENV LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    DOC_DIR=/var/www \
    PHD_DIR=/opt/phd

# 🔧 Dépendances nécessaires à PhD
RUN apk add --no-cache \
    git \
    icu-dev \
    libxml2-dev \
    libxslt-dev \
    gettext \
    autoconf \
    g++ \
    pkgconfig \
 && docker-php-ext-install intl xsl

# 📦 Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 🧩 Installer PhD et le package PHP (version stable compatible --addpackage)
RUN git clone https://github.com/php/phd.git "$PHD_DIR" \
 && cd "$PHD_DIR" \
 && git checkout c3e2d8c \
 && git clone https://github.com/php/phd-php.git "$PHD_DIR/phpdotnet/phd/Package/PHP" \
 && composer install --no-dev --no-progress --prefer-dist || true

WORKDIR $DOC_DIR

# 🧠 Formats et package par défaut
ENV FORMAT=php-chunked-xhtml
ENV PACKAGE=PHP

# 🔓 Donne les droits complets sur /var/www (utile sous Windows/WSL2)
RUN mkdir -p /var/www/fr/output && chmod -R 777 /var/www

# 📜 Script d’entrée
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
RUN chmod -R 777 /var/www
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

