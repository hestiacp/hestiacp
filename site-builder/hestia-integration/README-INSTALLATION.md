# 📋 Guide d'installation complet - Site Builder HestiaCP

## 🔑 1. Credentials PostgreSQL

### Où sont stockés les credentials ?

**Fichier unique de configuration :** `/opt/site-builder/backend/.env`

```env
# Credentials PostgreSQL - À MODIFIER
DB_HOST=localhost
DB_PORT=5432
DB_NAME=site_builder
DB_USER=site_builder_user
DB_PASSWORD=VotreMotDePasseSecurise123!  # <-- ICI

# Autres configs importantes
JWT_SECRET=votre_cle_jwt_generee        # <-- Générer avec: openssl rand -hex 64
HESTIA_SSO_SECRET=secret_partage        # <-- Doit être IDENTIQUE dans le fichier PHP
```

### Création de la base de données

```bash
# 1. Se connecter à PostgreSQL
sudo -u postgres psql

# 2. Créer l'utilisateur avec le mot de passe que vous voulez
CREATE USER site_builder_user WITH PASSWORD 'VotreMotDePasseSecurise123!';

# 3. Créer la base de données
CREATE DATABASE site_builder OWNER site_builder_user;

# 4. Donner les droits
GRANT ALL PRIVILEGES ON DATABASE site_builder TO site_builder_user;

# 5. Quitter
\q
```

**Important :** Le mot de passe que vous choisissez ici doit être reporté dans `/opt/site-builder/backend/.env`

---

## 🔘 2. Intégration du bouton dans HestiaCP

### Fichier à modifier

```
/usr/local/hestia/web/templates/pages/list_web.php
```

### Étape 1 : Copier le fichier de fonctions

```bash
# Copier le fichier PHP d'intégration
sudo cp /opt/site-builder/hestia-integration/sitebuilder-functions.php \
        /usr/local/hestia/web/inc/sitebuilder.php

# Éditer pour configurer le secret
sudo nano /usr/local/hestia/web/inc/sitebuilder.php
```

Dans ce fichier, modifier ces lignes :
```php
define('SITEBUILDER_URL', 'https://builder.votre-domaine.com');  // Votre URL
define('SITEBUILDER_SSO_SECRET', 'secret_partage');              // MÊME secret que dans .env
```

### Étape 2 : Modifier list_web.php

```bash
sudo nano /usr/local/hestia/web/templates/pages/list_web.php
```

**Ajouter AU DÉBUT du fichier (ligne 1) :**
```php
<?php require_once('/usr/local/hestia/web/inc/sitebuilder.php'); ?>
```

**Ajouter le bouton après la ligne 276** (après "Download Site") :

Chercher ce bloc :
```php
<li class="units-table-row-action" data-key-action="href">
    <a
        class="units-table-row-action-link"
        href="/download/site/?site=<?= $key ?>&token=<?= $_SESSION["token"] ?>"
        title="<?= _("Download Site") ?>"
    >
        <i class="fas fa-download icon-orange"></i>
        <span class="u-hide-desktop"><?= _("Download Site") ?></span>
    </a>
</li>
```

**Ajouter JUSTE APRÈS :**
```php
<li class="units-table-row-action" data-key-action="href">
    <a
        class="units-table-row-action-link"
        href="<?= sitebuilder_url($user, $key) ?>"
        target="_blank"
        rel="noopener"
        title="<?= _("Site Builder") ?>"
    >
        <i class="fas fa-palette icon-green"></i>
        <span class="u-hide-desktop"><?= _("Site Builder") ?></span>
    </a>
</li>
```

---

## 🌐 3. Configuration HTTP/Nginx

### Option A : Sous-domaine dédié (RECOMMANDÉ)

Créer le domaine `builder.votre-domaine.com` dans HestiaCP, puis :

```bash
# Copier la config Nginx personnalisée
sudo cp /opt/site-builder/hestia-integration/nginx-sitebuilder.conf \
        /etc/nginx/conf.d/sitebuilder.conf

# Éditer pour mettre votre domaine
sudo nano /etc/nginx/conf.d/sitebuilder.conf
# Remplacer <votre-domaine.com> par votre domaine

# Tester et recharger Nginx
sudo nginx -t && sudo systemctl reload nginx
```

### Option B : Utiliser le proxy HestiaCP existant

Si vous préférez ne pas modifier la config Nginx manuellement, vous pouvez :

1. Créer le domaine `builder.votre-domaine.com` dans HestiaCP
2. Activer le template "proxy" 
3. Configurer une redirection proxy vers `localhost:3001`

Dans HestiaCP > Web > builder.votre-domaine.com > Edit :
- Activer "Proxy Support"
- Proxy Template: custom ou default

Puis créer `/home/admin/conf/web/builder.votre-domaine.com/nginx.conf_proxy` :

```nginx
location / {
    proxy_pass http://127.0.0.1:5173;  # Frontend en dev, ou fichiers statiques en prod
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_cache_bypass $http_upgrade;
}

location /api {
    proxy_pass http://127.0.0.1:3001;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    client_max_body_size 50M;
}
```

---

## ✅ 4. Vérification rapide

### Test des credentials PostgreSQL
```bash
psql -h localhost -U site_builder_user -d site_builder -c "SELECT 1;"
# Doit demander le mot de passe et afficher "1"
```

### Test du backend
```bash
curl http://localhost:3001/health
# Doit retourner: {"success":true,"message":"Site Builder API is running",...}
```

### Test du SSO
```bash
cd /opt/site-builder/backend
node -e "
const sso = require('./src/services/ssoService');
const params = sso.generateSignature('admin', 'example.com');
console.log('URL de test:');
console.log('http://localhost:3001/api/auth/sso-redirect?' + new URLSearchParams(params).toString());
"
```

---

## 📁 Récapitulatif des fichiers importants

| Fichier | Contient |
|---------|----------|
| `/opt/site-builder/backend/.env` | **Tous les credentials** (DB, JWT, SSO) |
| `/usr/local/hestia/web/inc/sitebuilder.php` | Fonctions PHP + **secret SSO** (doit matcher) |
| `/usr/local/hestia/web/templates/pages/list_web.php` | Fichier HestiaCP à modifier |
| `/etc/nginx/conf.d/sitebuilder.conf` | Config Nginx pour le builder |

---

## ⚠️ Points de vigilance

1. **Le secret SSO doit être IDENTIQUE** dans :
   - `/opt/site-builder/backend/.env` → `HESTIA_SSO_SECRET`
   - `/usr/local/hestia/web/inc/sitebuilder.php` → `SITEBUILDER_SSO_SECRET`

2. **Permissions de publication** : Le process Node doit pouvoir écrire dans les dossiers web :
   ```bash
   # Ajouter l'utilisateur node au groupe www-data (ou hestia)
   sudo usermod -aG www-data nodeuser
   ```

3. **Certificat SSL** : En production, utiliser Let's Encrypt :
   ```bash
   sudo certbot certonly --nginx -d builder.votre-domaine.com
   ```
