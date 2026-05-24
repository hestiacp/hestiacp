#!/bin/bash
# Installer for Fix Web Permissions module

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HESTIA_BIN="/usr/local/hestia/bin"

echo "--- Installing v-fix-web-permissions ---"

# Script
target_bin="$HESTIA_BIN/v-fix-web-permissions"
[ -f "$target_bin" ] && cp "$target_bin" "${target_bin}.bak.$(date +%Y%m%d-%H%M%S)"
cp "$SCRIPT_DIR/v-fix-web-permissions" "$target_bin"
chmod +x "$target_bin"
echo "  -> [OK] Installed script: $target_bin"
