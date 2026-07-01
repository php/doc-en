#!/usr/bin/env bash

set -e

FORMAT="${1:-xhtml}"

case "$FORMAT" in
  xhtml) OUTDIR="output/php-chunked-xhtml" ;;
  php)   OUTDIR="output/php-web" ;;
  *)     echo "Usage: $0 [xhtml|php]" >&2; exit 1 ;;
esac

 # doc-base invokes `java -jar jing.jar` with no flags, so the only handle on
 # the JAXP entity-size limit is the JVM env vars. The PHP manual is well past
 # the 100k default and we have no other way to lift the limit.
 export _JAVA_OPTIONS='-Djdk.xml.totalEntitySizeLimit=0 -Djdk.xml.entityExpansionLimit=0 -Djdk.xml.maxGeneralEntitySizeLimit=0'

php ../doc-base/configure.php \
    --disable-libxml-check \
    --enable-xml-details \
    --redirect-stderr-to-stdout \
    --with-lang=en

php -d memory_limit=512M ../phd/render.php \
    --docbook ../doc-base/.manual.xml \
    --output ./output \
    --package PHP \
    --format "$FORMAT"

pkill -f 'php -S 0.0.0.0:8080' 2>/dev/null || true
exec php -S 0.0.0.0:8080 -t "$OUTDIR"
