#!/usr/bin/env bash

set -e

WORKSPACE="$(cd "$(dirname "$0")/.." && pwd)"
PARENT="$(dirname "$WORKSPACE")"
OWNER="$(stat -c '%U' "$WORKSPACE")"

# Clone doc-base and phd as siblings of doc-en
[ -d "$PARENT/doc-base" ] || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/doc-base.git
[ -d "$PARENT/phd" ]      || sudo -u "$OWNER" git -C "$PARENT" clone --depth 1 https://github.com/php/phd.git

# Pre-create the served directory
sudo -u "$OWNER" mkdir -p "$WORKSPACE/output/php-chunked-xhtml"

cat <<'EOF'

  Devcontainer ready.

  Build & serve:     F5 > "Build XHTML & serve"   (or "Build PHP web & serve")

  View them:         http://localhost:8080

EOF
