FROM php:8.2-cli

RUN apt-get update && \
    apt-get install -y git default-jre-headless

WORKDIR /var/www

ADD https://api.github.com/repos/php/phd/git/refs/heads/master version-phd.json
ADD https://api.github.com/repos/php/doc-base/git/refs/heads/master version-doc-base.json

RUN git clone --depth 1 https://github.com/php/phd.git &&  \
    git clone --depth 1 https://github.com/php/doc-base.git && \
    chown -R 1000:1000 /var/www

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/local.ini

ENV FORMAT=xhtml

CMD ["sh", "-c", "php doc-base/configure.php --disable-segfault-error && \
    php phd/render.php --docbook doc-base/.manual.xml --output=/var/www/en/output --package PHP --format ${FORMAT}"]
