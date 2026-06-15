#!/usr/bin/env bash

set -e

FORMAT="${1:-xhtml}"

case "$FORMAT" in
  xhtml) DOCROOT="output/php-chunked-xhtml" ;;
  php)   DOCROOT="../web-php" ;;
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

# Restart any existing server, then launch the new one in its own session so
# Ctrl+C in the debug terminal only kills the log tail below. The server keeps
# running until the next build replaces it (or the container shuts down).
#
# auto_prepend_file rewrites $_SERVER from the request's Host header so the PHP
# format's $MYSITE-built URLs work behind Codespaces / other port forwarders.
LOG="/tmp/php-server-${FORMAT}.log"
PREPEND="$(cd "$(dirname "$0")" && pwd)/server-prepend.php"
pkill -f 'php -S 0.0.0.0:8080' 2>/dev/null || true
setsid nohup php -d "auto_prepend_file=$PREPEND" -S 0.0.0.0:8080 -t "$DOCROOT" \
    >"$LOG" 2>&1 </dev/null &
SERVER_PID=$!

cat <<EOF

  Server: http://localhost:8080  (pid $SERVER_PID, doc root: $DOCROOT)
  Logs:   $LOG
  Ctrl+C exits this tail; the server keeps running.

EOF

exec tail -f "$LOG"
