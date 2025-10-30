#!/bin/sh

# POSIX-compliant entrypoint for building PHP documentation
# Fully compatible with Linux, Alpine, and macOS environments

set -e

LANGUAGE=${1:-en}
FORMAT=${2:-xhtml}
PACKAGE=${PACKAGE:-PHP}
MANUAL_PATH="/var/www/$LANGUAGE/.manual.xml"

echo "🧩 Building PHP documentation..."
echo "Language: $LANGUAGE"
echo "Format:   $FORMAT"
echo "Docbook:  $MANUAL_PATH"

# Add the PHP package to the include_path for PhD.
export PHP_INCLUDE_PATH="$PHD_DIR/phpdotnet/phd/Package:$PHD_DIR/phpdotnet/phd/Package/PHP:$PHP_INCLUDE_PATH"

# 🧹 Clean if necessary
if [ -f "$MANUAL_PATH" ]; then
  echo "🧹 Removing old .manual.xml..."
  rm -f "$MANUAL_PATH" || true
fi

echo "ℹ️ Generating .manual.xml for $LANGUAGE..."
cd /var/www/doc-base
php configure.php \
  --disable-segfault-error \
  --basedir="/var/www/doc-base" \
  --output="$MANUAL_PATH" \
  --lang="$LANGUAGE"

if [ ! -f "$MANUAL_PATH" ]; then
  echo "❌ Failed to generate $MANUAL_PATH"
  exit 1
fi
echo "✅ Generated $MANUAL_PATH"

# Verify PHP package presence
if [ ! -d "$PHD_DIR/phpdotnet/phd/Package/PHP" ]; then
  echo "❌ PHP package not found in $PHD_DIR/phpdotnet/phd/Package/PHP"
  exit 1
fi

# Normalize Windows CRLF endings and adjust doc-base paths
echo "🩹 Patching entity paths to use doc-base…"
tr -d '\r' < "$MANUAL_PATH" > "$MANUAL_PATH.clean" && mv "$MANUAL_PATH.clean" "$MANUAL_PATH"
sed -i "s#/var/www/$LANGUAGE/entities/#/var/www/doc-base/entities/#g" "$MANUAL_PATH"
sed -i "s#/var/www/$LANGUAGE/version.xml#/var/www/doc-base/version.xml#g" "$MANUAL_PATH"
sed -i "s#/var/www/$LANGUAGE/sources.xml#/var/www/doc-base/sources.xml#g" "$MANUAL_PATH"
head -n 25 "$MANUAL_PATH"

# 🧩 Inclure le package PHP pour PhD
export PHP_INCLUDE_PATH="$PHD_DIR/phpdotnet/phd/Package:$PHD_DIR/phpdotnet/phd/Package/PHP:$PHP_INCLUDE_PATH"

cd /var/www/$LANGUAGE

# 🏗️ Render the documentation
php -d include_path="$PHP_INCLUDE_PATH" \
  "$PHD_DIR/render.php" \
  --docbook "$MANUAL_PATH" \
  --package "$PACKAGE" \
  --format "$FORMAT"

# 🔎 Locate the output directory
OUTDIR=""
for d in php-chunked-xhtml xhtml phpweb php; do
  if [ -d "/var/www/$LANGUAGE/output/$d" ]; then
    OUTDIR="/var/www/$LANGUAGE/output/$d"
    break
  fi
done

if [ -d "$OUTDIR" ]; then
  echo "✅ Build complete: $OUTDIR"
else
  echo "❌ No output directory found in /var/www/$LANGUAGE/output/"
  ls -R "/var/www/$LANGUAGE/output" || true
  exit 1
fi
