#!/bin/bash
# Installer for Logrotate Optimize module

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HESTIA_BIN="/usr/local/hestia/bin"

echo "--- Installing v-optimize-logrotate ---"

# Script
target_bin="$HESTIA_BIN/v-optimize-logrotate"
[ -f "$target_bin" ] && cp "$target_bin" "${target_bin}.bak.$(date +%Y%m%d-%H%M%S)"
cp "$SCRIPT_DIR/v-optimize-logrotate" "$target_bin"
chmod +x "$target_bin"
echo "  -> [OK] Installed script: $target_bin"

echo ""
echo "To apply the optimization, run:"
echo "  v-optimize-logrotate              # Apply + force immediate rotation"
echo "  v-optimize-logrotate --dry-run    # Preview changes without applying"
echo ""
