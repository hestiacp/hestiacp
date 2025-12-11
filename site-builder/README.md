# HestiaCP Site Builder

🎨 **Un builder de sites web moderne et intuitif, intégré à HestiaCP.**

Créez et publiez facilement des sites web statiques directement depuis votre panneau HestiaCP, sans aucune connaissance en programmation.

![Site Builder Preview](https://placehold.co/800x400/3b82f6/ffffff?text=Site+Builder+Preview)

## ✨ Fonctionnalités

- **Éditeur visuel drag & drop** basé sur GrapesJS
- **Blocs pré-conçus** : Header, Hero, Galerie, Contact, Footer...
- **Gestion multi-pages** : créez autant de pages que nécessaire
- **Preview responsive** : bureau, tablette, mobile
- **Publication en un clic** vers le dossier web HestiaCP
- **SSO intégré** : connexion automatique depuis HestiaCP
- **Design moderne** et interface intuitive

## 🏗️ Architecture

```
site-builder/
├── backend/                 # API Node.js + Express
│   ├── src/
│   │   ├── config/         # Configuration (DB, JWT, etc.)
│   │   ├── controllers/    # Logique métier
│   │   ├── middleware/     # Auth, erreurs
│   │   ├── models/         # Modèles Sequelize (User, Project, Page)
│   │   ├── routes/         # Routes API REST
│   │   └── services/       # Services (SSO, Publication)
│   └── server.js           # Point d'entrée
├── frontend/               # React + Vite
│   ├── src/
│   │   ├── components/     # Composants React
│   │   │   ├── Builder/    # Éditeur principal
│   │   │   ├── blocks/     # Blocs custom GrapesJS
│   │   │   └── common/     # Composants réutilisables
│   │   ├── pages/          # Pages de l'application
│   │   ├── services/       # API client
│   │   └── styles/         # CSS personnalisé
│   └── index.html
└── docker-compose.yml      # Config Docker
```

## 🚀 Installation

### Prérequis

- Node.js 18+ 
- PostgreSQL 13+
- npm ou yarn

### 1. Cloner et configurer

```bash
# Cloner le repository
cd /path/to/hestiacp
git clone <repo-url> site-builder
cd site-builder

# Copier la configuration
cp backend/.env.example backend/.env

# Éditer la configuration
nano backend/.env
```

### 2. Configuration backend (.env)

```env
# Serveur
NODE_ENV=production
PORT=3001
FRONTEND_URL=https://builder.votre-domaine.com

# Base de données PostgreSQL
DB_HOST=localhost
DB_PORT=5432
DB_NAME=site_builder
DB_USER=site_builder_user
DB_PASSWORD=votre_mot_de_passe_securise

# JWT (générer avec: openssl rand -hex 64)
JWT_SECRET=votre_cle_secrete_tres_longue
JWT_EXPIRES_IN=7d

# SSO HestiaCP (même secret des deux côtés)
HESTIA_SSO_SECRET=secret_partage_avec_hestia

# Chemin de publication
PUBLISH_BASE_PATH=/home/{USERNAME}/web/{DOMAIN}/public_html
```

### 3. Installation des dépendances

```bash
# Backend
cd backend
npm install

# Frontend
cd ../frontend
npm install
```

### 4. Créer la base de données

```bash
# Se connecter à PostgreSQL
sudo -u postgres psql

# Créer l'utilisateur et la base
CREATE USER site_builder_user WITH PASSWORD 'votre_mot_de_passe';
CREATE DATABASE site_builder OWNER site_builder_user;
GRANT ALL PRIVILEGES ON DATABASE site_builder TO site_builder_user;
\q
```

### 5. Démarrer les services

**Développement :**

```bash
# Terminal 1 - Backend
cd backend
npm run dev

# Terminal 2 - Frontend
cd frontend
npm run dev
```

**Production :**

```bash
# Build du frontend
cd frontend
npm run build

# Démarrer le backend (avec PM2 recommandé)
cd ../backend
pm2 start server.js --name sitebuilder-api
```

### 6. Configuration avec Docker (optionnel)

```bash
# Démarrer tous les services
docker-compose up -d

# Voir les logs
docker-compose logs -f

# Arrêter
docker-compose down
```

## 🔗 Intégration HestiaCP

### 1. Ajouter le bouton Site Builder

Éditez le fichier `/usr/local/hestia/web/templates/pages/list_web.php` et ajoutez :

```php
<?php
// Fonction pour générer le lien SSO
function generateSiteBuilderUrl($username, $domain) {
    $secret = 'votre_secret_sso'; // Même que HESTIA_SSO_SECRET
    $builderUrl = 'https://builder.votre-domaine.com';
    
    $timestamp = time();
    $dataToSign = "$username:$domain:$timestamp";
    $signature = hash_hmac('sha256', $dataToSign, $secret);
    
    $params = http_build_query([
        'h_user' => $username,
        'h_domain' => $domain,
        'h_timestamp' => $timestamp,
        'h_sig' => $signature
    ]);
    
    return "$builderUrl/api/auth/sso-redirect?$params";
}
?>

<!-- Dans la boucle des domaines, ajouter le bouton -->
<a href="<?= generateSiteBuilderUrl($user, $domain) ?>" 
   target="_blank" 
   class="button button-secondary">
    <i class="fas fa-paint-brush"></i> Site Builder
</a>
```

### 2. Configuration Nginx pour le builder

Créez `/etc/nginx/conf.d/sitebuilder.conf` :

```nginx
server {
    listen 80;
    server_name builder.votre-domaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name builder.votre-domaine.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Frontend (fichiers statiques)
    root /path/to/site-builder/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # API Backend
    location /api {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### 3. Permissions de publication

Pour que le service Node.js puisse écrire dans les dossiers web :

**Option A - Groupe partagé (recommandé) :**

```bash
# Créer un groupe
sudo groupadd sitebuilder

# Ajouter le user Node.js et www-data au groupe
sudo usermod -aG sitebuilder nodeuser
sudo usermod -aG sitebuilder www-data

# Pour chaque utilisateur HestiaCP dont le domaine utilise le builder
sudo chmod g+rwx /home/USERNAME/web/DOMAIN/public_html
sudo chgrp -R sitebuilder /home/USERNAME/web/DOMAIN/public_html
```

**Option B - ACL :**

```bash
sudo setfacl -R -m u:nodeuser:rwx /home/*/web/*/public_html
sudo setfacl -R -d -m u:nodeuser:rwx /home/*/web/*/public_html
```

## 📚 API Reference

### Authentification

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/auth/sso` | POST | Authentification SSO |
| `/api/auth/sso-redirect` | GET | SSO avec redirection |
| `/api/auth/me` | GET | Utilisateur connecté |
| `/api/auth/logout` | POST | Déconnexion |

### Projets

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/projects` | GET | Liste des projets |
| `/api/projects` | POST | Créer un projet |
| `/api/projects/:id` | GET | Détails d'un projet |
| `/api/projects/:id` | PUT | Modifier un projet |
| `/api/projects/:id` | DELETE | Supprimer un projet |
| `/api/projects/:id/publish` | POST | Publier le site |

### Pages

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/projects/:id/pages` | GET | Liste des pages |
| `/api/projects/:id/pages` | POST | Créer une page |
| `/api/projects/:id/pages/:pageId` | GET | Détails d'une page |
| `/api/projects/:id/pages/:pageId` | PUT | Modifier une page |
| `/api/projects/:id/pages/:pageId` | DELETE | Supprimer une page |

## 🎨 Blocs disponibles

- **Header** : Navigation avec logo et liens
- **Hero Section** : Bandeau d'accueil avec CTA
- **Texte + Image** : Section de contenu mixte
- **Galerie** : Grille d'images avec overlay
- **Formulaire de contact** : Formulaire complet
- **Témoignages** : Carousel de témoignages
- **Fonctionnalités** : Grille de features
- **Call To Action** : Bannière d'action
- **Footer** : Pied de page complet

## 🔧 Extension

### Ajouter un bloc personnalisé

```javascript
// frontend/src/components/blocks/index.js

blockManager.add('my-custom-block', {
  label: 'Mon Bloc',
  category: 'Sections',
  media: '<svg>...</svg>',
  content: `
    <section class="my-block">
      <h2>Mon titre</h2>
      <p>Mon contenu</p>
    </section>
    <style>
      .my-block { /* styles */ }
    </style>
  `
});
```

### Ajouter un nouveau modèle

1. Créer le modèle dans `backend/src/models/`
2. L'importer dans `backend/src/models/index.js`
3. Créer le contrôleur correspondant
4. Ajouter les routes

## 🐛 Dépannage

### Erreur de connexion DB

```bash
# Vérifier que PostgreSQL est démarré
sudo systemctl status postgresql

# Vérifier les credentials
psql -h localhost -U site_builder_user -d site_builder
```

### Erreur de publication

```bash
# Vérifier les permissions
ls -la /home/USERNAME/web/DOMAIN/public_html

# Vérifier les logs du backend
tail -f backend/logs/combined.log
```

### Erreur SSO

```bash
# Tester la génération de signature
cd backend
node -e "
const sso = require('./src/services/ssoService');
console.log(sso.generateSignature('testuser', 'example.com'));
"
```

## 📄 Licence

GPL-3.0 - Voir le fichier [LICENSE](../LICENSE) pour plus de détails.

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou une pull request.

---

**Développé avec ❤️ pour la communauté HestiaCP**
