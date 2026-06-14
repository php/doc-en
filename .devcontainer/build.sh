#!/usr/bin/env bash

set -e

FORMAT="${1:-xhtml}"

case "$FORMAT" in
  xhtml) OUTDIR="output/php-chunked-xhtml" ;;
  php)   OUTDIR="output/php-web" ;;
  *)     echo "Usage: $0 [xhtml|php]" >&2; exit 1 ;;
esac

php ../doc-base/configure.php --with-lang=en
php ../phd/render.php \
    --docbook ../doc-base/.manual.xml \
    --output ./output \
    --package PHP \
    --format "$FORMAT"

pkill -f 'php -S 0.0.0.0:8080' 2>/dev/null || true
exec php -S 0.0.0.0:8080 -t "$OUTDIR"
