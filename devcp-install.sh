#!/bin/bash

# =========================
# 🚀 DevCP Install Script (Robust)
# Made with ❤️ by Dev-IT
# =========================

# Exit immediately on any error
set -euo pipefail

# Colors for terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

function log_info() {
    echo -e "${GREEN}[INFO] $*${NC}"
}
function log_warn() {
    echo -e "${YELLOW}[WARN] $*${NC}"
}
function log_error() {
    echo -e "${RED}[ERROR] $*${NC}"
}

# Root check
if [[ $EUID -ne 0 ]]; then
    log_error "Ce script doit être exécuté en tant que root."
    exit 1
fi

log_info "Bienvenue dans le script d'installation DevCP !"
echo "██████╗ ███████╗██╗   ██╗ ██████╗██████╗ "
echo "██╔══██╗██╔════╝╚██╗ ██╔╝██╔════╝██╔══██╗"
echo "██████╔╝█████╗   ╚████╔╝ ██║     ██████╔╝"
echo "██╔═══╝ ██╔══╝    ╚██╔╝  ██║     ██╔═══╝ "
echo "██║     ███████╗   ██║   ╚██████╗██║     "
echo "╚═╝     ╚══════╝   ╚═╝    ╚═════╝╚═╝     "
echo "          ⚡ DevCP Installer ⚡          "

export DEBIAN_FRONTEND=noninteractive

log_info "Mise à jour du système (apt update && apt upgrade -y)..."
apt update -y && apt upgrade -y

# Download the official installer script
INSTALLER_URL="https://raw.githubusercontent.com/Ghost-Dev9/DevCP/main/install/dst-install.sh"
INSTALLER_FILE="dst-install.sh"

log_info "Téléchargement du script d’installation DevCP..."
if ! wget -q -O "$INSTALLER_FILE" "$INSTALLER_URL"; then
    log_error "Échec du téléchargement du script d'installation ($INSTALLER_URL)."
    exit 2
fi

log_info "Vérification de l'intégrité du script téléchargé..."
if ! grep -q "DevCP" "$INSTALLER_FILE"; then
    log_warn "Le script ne contient pas la mention 'DevCP'. Veuillez vérifier l'URL ou le script téléchargé."
    rm -f "$INSTALLER_FILE"
    exit 3
fi

chmod +x "$INSTALLER_FILE"

log_info "Lancement de l’installation avec la configuration DevCP..."
if ! bash "$INSTALLER_FILE" \
    --nginx yes \
    --apache no \
    --phpfpm yes \
    --mysql yes \
    --ssh yes \
    --exim no \
    --dovecot no \
    --clamav no \
    --spamassassin no \
    --firewall yes; then
    log_error "Échec de l'exécution de l'installateur DevCP."
    rm -f "$INSTALLER_FILE"
    exit 4
fi

log_info "Nettoyage des fichiers temporaires..."
rm -f "$INSTALLER_FILE"

log_info "✅ DevCP est maintenant installé avec succès !"
echo -e "➡️  Accédez à votre panneau via : https://<votre-ip>:8083"
echo -e "🧠 Identifiants par défaut : admin / mot de passe affiché à la fin de l'installation"
echo -e "🔐 N'oubliez pas de sécuriser l'accès après installation."
