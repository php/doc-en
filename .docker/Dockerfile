FROM php:8.2-cli

RUN apt-get update ; \
    apt-get install -y git

WORKDIR /var/www

RUN git clone https://github.com/php/phd.git ;  \
    git clone https://github.com/php/doc-base.git ; \
    git -C phd checkout 5708881e233cd9979dd11bcbd9fd0bb8a714f9d5

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/local.ini

CMD php doc-base/configure.php --disable-segfault-error ; \
    php phd/render.php --docbook doc-base/.manual.xml --output=/var/www/en/output --package PHP --format xhtml
