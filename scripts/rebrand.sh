#!/bin/bash

# ─── CONFIG ───────────────────────────────────────────────────────────────
OLD_NAME_LOWER="devit"
OLD_NAME_UPPER="DEVIT"
OLD_NAME_CAP="DevIT"

NEW_NAME_LOWER="devit"
NEW_NAME_UPPER="DEVIT"
NEW_NAME_CAP="DevIT"

# Exclusions (ajoute ici les dossiers à ignorer genre install, .git, etc.)
EXCLUDE_DIRS="install .git .vscode vendor"

# Extensions concernées
EXTENSIONS="sh php pl conf py js css html txt md xml json yml ini"

# ─── SCRIPT ───────────────────────────────────────────────────────────────
echo "🔥 Rebranding $OLD_NAME_CAP → $NEW_NAME_CAP..."

for EXT in $EXTENSIONS; do
    for FILE in $(find . -type f -name "*.${EXT}"); do
        SKIP=0
        for EX in $EXCLUDE_DIRS; do
            if [[ "$FILE" == *"/$EX/"* ]]; then
                SKIP=1
                break
            fi
        done
        [[ $SKIP -eq 1 ]] && continue

        echo "📄 Updating $FILE"
        sed -i "s/$OLD_NAME_UPPER/$NEW_NAME_UPPER/g" "$FILE"
        sed -i "s/$OLD_NAME_CAP/$NEW_NAME_CAP/g" "$FILE"
        sed -i "s/$OLD_NAME_LOWER/$NEW_NAME_LOWER/g" "$FILE"
    done
done

echo "✅ Rebranding complete. Don't forget to git commit & push!"
