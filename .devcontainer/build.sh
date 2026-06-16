#!/usr/bin/env bash

set -e

FORMAT="${1:-xhtml}"

case "$FORMAT" in
  xhtml) DOCROOT="output/php-chunked-xhtml" ;;
  php)   DOCROOT="../web-php" ;;
  *)     echo "Usage: $0 [xhtml|php]" >&2; exit 1 ;;
esac

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
LOG="/tmp/php-server-${FORMAT}.log"
pkill -f 'php -S 0.0.0.0:8080' 2>/dev/null || true
setsid nohup php -S 0.0.0.0:8080 -t "$DOCROOT" \
    >"$LOG" 2>&1 </dev/null &
SERVER_PID=$!

cat <<EOF

  Server: http://localhost:8080  (pid $SERVER_PID, doc root: $DOCROOT)
  Logs:   $LOG
  Ctrl+C exits this tail; the server keeps running.

EOF

exec tail -f "$LOG"
