#!/bin/bash

# =========================
# 🚀 DevCP Install Script
# Made with ❤️ by Dev-IT
# =========================

# Couleurs terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}"
echo "██████╗ ███████╗██╗   ██╗ ██████╗██████╗ "
echo "██╔══██╗██╔════╝╚██╗ ██╔╝██╔════╝██╔══██╗"
echo "██████╔╝█████╗   ╚████╔╝ ██║     ██████╔╝"
echo "██╔═══╝ ██╔══╝    ╚██╔╝  ██║     ██╔═══╝ "
echo "██║     ███████╗   ██║   ╚██████╗██║     "
echo "╚═╝     ╚══════╝   ╚═╝    ╚═════╝╚═╝     "
echo "          ⚡ DevCP Installer ⚡          "
echo -e "${NC}"

# Vérifie si root
if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}❌ Ce script doit être exécuté en tant que root.${NC}"
    exit 1
fi

# Mise à jour
apt update && apt upgrade -y

# Téléchargement du script officiel modifié
echo -e "${GREEN}📥 Téléchargement du script d’installation DevCP...${NC}"
wget -q https://raw.githubusercontent.com/Ghost-Dev9/DevCP/refs/heads/main/install/hst-install.sh

# Donner les droits d’exécution
chmod +x hst-install.sh

# Lancer l’installation avec les options souhaitées
echo -e "${GREEN}🚀 Lancement de l’installation avec la configuration DevCP...${NC}"
bash hst-install.sh \
  --nginx yes \
  --apache no \
  --phpfpm yes \
  --mysql yes \
  --ssh yes \
  --exim no \
  --dovecot no \
  --clamav no \
  --spamassassin no \
  --firewall yes

# Nettoyage
rm -f hst-install.sh

echo -e "${GREEN}✅ DevCP est maintenant installé avec succès !"
echo -e "➡️ Accédez à votre panneau via : https://<votre-ip>:8083"
echo -e "🧠 Identifiants par défaut : admin / votre mot de passe affiché à la fin de l'installation"
echo -e "🔐 N'oubliez pas de sécuriser l'accès après installation.${NC}"
