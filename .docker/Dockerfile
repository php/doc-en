FROM php:8.2-cli-alpine

LABEL maintainer="PHP Documentation Team <doc@php.net>"
LABEL description="Image for building and serving PHP documentation (php-chunked-xhtml format)"

ENV LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    DOC_DIR=/var/www \
    PHD_DIR=/opt/phd

# 🔧 Dependancies to PhD
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

# Install PhD and the PHP package (stable compatible version --addpackage)
RUN git clone https://github.com/php/phd.git "$PHD_DIR" \
 && cd "$PHD_DIR" \
 && git checkout c3e2d8c \
 && git clone https://github.com/php/phd-php.git "$PHD_DIR/phpdotnet/phd/Package/PHP" \
 && composer install --no-dev --no-progress --prefer-dist || true

WORKDIR $DOC_DIR

# Default formats and package
ENV FORMAT=php-chunked-xhtml
ENV PACKAGE=PHP

# Grant full permissions to /var/www (useful on Windows/WSL2)
RUN mkdir -p /var/www/output && chmod -R 777 /var/www

# Entry script
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
RUN chmod -R 777 /var/www
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

