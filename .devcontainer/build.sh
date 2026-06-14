#!/usr/bin/env bash

set -e

FORMAT="${1:-xhtml}"
LANG="${LANG:-en}"

WORKSPACE="$(cd "$(dirname "$0")/.." && pwd)"
PARENT="$(dirname "$WORKSPACE")"

cd "$WORKSPACE"
php "$PARENT/doc-base/configure.php" --with-lang="$LANG"
php "$PARENT/phd/render.php" \
  --docbook "$PARENT/doc-base/.manual.xml" \
  --output "$WORKSPACE/output" \
  --package PHP \
  --format "$FORMAT"
