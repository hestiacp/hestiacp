#!/bin/bash

# Rebranding script: "DevIT" → "DevIT"
# Respecte la casse, n'affecte pas les fichiers d'installation sensibles

set -e

# Répertoire racine du projet (adapter si besoin)
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# Extensions de fichiers à traiter (adapter si besoin)
EXTENSIONS="sh php py js json yml yaml conf ini txt md html css tpl xml"

# Fichiers à exclure (installateurs et scripts critiques)
EXCLUDES="install.sh setup.sh installer.sh install/ setup/ scripts/install* scripts/setup*"

# Fonction de recherche/exclusion
find_files() {
    local ext
    for ext in $EXTENSIONS; do
        find . -type f -name "*.${ext}" \
            $(for excl in $EXCLUDES; do echo "! -path \"*${excl}*\""; done)
    done
}

# Mapping des remplacements
declare -A REPLACEMENTS=(
    ["DevIT"]="DevIT"
    ["DevIT"]="DevIT"
    ["DEVIT"]="DEVIT"
)

echo "Démarrage du rebranding de 'DevIT' vers 'DevIT'..."

for file in $(find_files); do
    for from in "${!REPLACEMENTS[@]}"; do
        to="${REPLACEMENTS[$from]}"
        # Utilisation de perl pour le remplacement respectant la casse
        perl -pi -e "s/\b$from\b/$to/g" "$file"
    done
done

echo "Rebranding terminé !"

exit 0

