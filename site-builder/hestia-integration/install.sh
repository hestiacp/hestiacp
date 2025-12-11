#!/bin/bash
# ===========================================
# Script d'installation du Site Builder
# ===========================================
#
# Ce script installe le Site Builder sur un serveur HestiaCP.
#
# Usage:
#   chmod +x install.sh
#   sudo ./install.sh
#
# Prérequis:
#   - HestiaCP installé
#   - Node.js 18+
#   - PostgreSQL 13+
#

set -e

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
INSTALL_DIR="/opt/site-builder"
DB_NAME="site_builder"
DB_USER="site_builder_user"
BUILDER_DOMAIN=""

echo -e "${GREEN}"
echo "=============================================="
echo "   HestiaCP Site Builder - Installation"
echo "=============================================="
echo -e "${NC}"

# Vérifier les privilèges root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}Ce script doit être exécuté en tant que root${NC}"
   exit 1
fi

# Demander les informations
read -p "Domaine pour le builder (ex: builder.example.com): " BUILDER_DOMAIN
read -sp "Mot de passe PostgreSQL pour site_builder_user: " DB_PASSWORD
echo
read -sp "Secret SSO partagé avec HestiaCP: " SSO_SECRET
echo
read -sp "Secret JWT (appuyez sur Entrée pour générer): " JWT_SECRET
if [ -z "$JWT_SECRET" ]; then
    JWT_SECRET=$(openssl rand -hex 64)
    echo -e "${GREEN}JWT Secret généré${NC}"
fi
echo

echo -e "${YELLOW}=== Vérification des prérequis ===${NC}"

# Vérifier Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}Node.js n'est pas installé. Installation...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi
NODE_VERSION=$(node -v)
echo -e "${GREEN}Node.js: $NODE_VERSION${NC}"

# Vérifier PostgreSQL
if ! command -v psql &> /dev/null; then
    echo -e "${RED}PostgreSQL n'est pas installé${NC}"
    exit 1
fi
echo -e "${GREEN}PostgreSQL: OK${NC}"

# Vérifier PM2
if ! command -v pm2 &> /dev/null; then
    echo "Installation de PM2..."
    npm install -g pm2
fi
echo -e "${GREEN}PM2: OK${NC}"

echo -e "${YELLOW}=== Création de la base de données ===${NC}"

# Créer l'utilisateur et la base PostgreSQL
sudo -u postgres psql <<EOF
DO \$\$
BEGIN
   IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '$DB_USER') THEN
      CREATE ROLE $DB_USER LOGIN PASSWORD '$DB_PASSWORD';
   END IF;
END
\$\$;
CREATE DATABASE IF NOT EXISTS $DB_NAME OWNER $DB_USER;
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
EOF

echo -e "${GREEN}Base de données créée${NC}"

echo -e "${YELLOW}=== Installation des fichiers ===${NC}"

# Créer le répertoire d'installation
mkdir -p $INSTALL_DIR
cd $INSTALL_DIR

# Copier les fichiers (supposant qu'ils sont dans le répertoire courant)
if [ -d "../site-builder" ]; then
    cp -r ../site-builder/* .
fi

# Créer le fichier .env du backend
cat > $INSTALL_DIR/backend/.env <<EOF
NODE_ENV=production
PORT=3001
FRONTEND_URL=https://$BUILDER_DOMAIN

DB_HOST=localhost
DB_PORT=5432
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD

JWT_SECRET=$JWT_SECRET
JWT_EXPIRES_IN=7d

HESTIA_SSO_SECRET=$SSO_SECRET
PUBLISH_BASE_PATH=/home/{USERNAME}/web/{DOMAIN}/public_html

LOG_LEVEL=info
EOF

echo -e "${GREEN}Configuration créée${NC}"

echo -e "${YELLOW}=== Installation des dépendances ===${NC}"

# Backend
cd $INSTALL_DIR/backend
npm install --production

# Frontend
cd $INSTALL_DIR/frontend
npm install
npm run build

echo -e "${GREEN}Dépendances installées${NC}"

echo -e "${YELLOW}=== Configuration de PM2 ===${NC}"

cd $INSTALL_DIR
pm2 start hestia-integration/pm2-ecosystem.config.js
pm2 save
pm2 startup

echo -e "${GREEN}PM2 configuré${NC}"

echo -e "${YELLOW}=== Configuration Nginx ===${NC}"

# Copier la config Nginx
cp $INSTALL_DIR/hestia-integration/nginx-sitebuilder.conf /etc/nginx/conf.d/
sed -i "s/<votre-domaine.com>/$BUILDER_DOMAIN/g" /etc/nginx/conf.d/nginx-sitebuilder.conf

# Tester et recharger Nginx
nginx -t && systemctl reload nginx

echo -e "${GREEN}Nginx configuré${NC}"

echo -e "${YELLOW}=== Certificat SSL ===${NC}"
echo "Génération du certificat Let's Encrypt..."
certbot certonly --nginx -d $BUILDER_DOMAIN --non-interactive --agree-tos --email admin@$BUILDER_DOMAIN || true

echo ""
echo -e "${GREEN}=============================================="
echo "   Installation terminée !"
echo "=============================================="
echo ""
echo "Le Site Builder est accessible sur:"
echo "  https://$BUILDER_DOMAIN"
echo ""
echo "Pour intégrer dans HestiaCP:"
echo "  1. Copiez sitebuilder-button.php dans /usr/local/hestia/web/inc/"
echo "  2. Configurez le secret SSO dans ce fichier"
echo "  3. Ajoutez le bouton dans list_web.php"
echo ""
echo "Commandes utiles:"
echo "  pm2 status              - Voir le status"
echo "  pm2 logs sitebuilder-api - Voir les logs"
echo "  pm2 restart sitebuilder-api - Redémarrer"
echo -e "${NC}"
