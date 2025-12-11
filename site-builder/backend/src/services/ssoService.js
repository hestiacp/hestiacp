/**
 * ===========================================
 * Service SSO HestiaCP
 * ===========================================
 * 
 * Gère la vérification des signatures SSO et la communication
 * avec HestiaCP pour l'authentification des utilisateurs.
 * 
 * Méthode de signature (simplifié pour MVP):
 * signature = HMAC-SHA256(username + domain + timestamp, secret)
 * 
 * En production, implémenter une vérification plus robuste avec:
 * - Timestamp pour éviter le replay
 * - Nonce unique par requête
 * - Vérification de l'IP source
 */

const crypto = require('crypto');
const config = require('../config');
const logger = require('../config/logger');

/**
 * Vérifie la signature SSO envoyée par HestiaCP
 * 
 * @param {object} params - Paramètres de la requête SSO
 * @param {string} params.username - Nom d'utilisateur HestiaCP
 * @param {string} params.domain - Domaine associé
 * @param {string} params.signature - Signature à vérifier
 * @param {string} params.timestamp - Timestamp de la requête (optionnel)
 * @returns {boolean} True si la signature est valide
 */
function verifySignature({ username, domain, signature, timestamp }) {
  try {
    const secret = config.hestia.ssoSecret;

    // Vérification du timestamp (optionnel mais recommandé)
    if (timestamp) {
      const requestTime = parseInt(timestamp, 10);
      const now = Math.floor(Date.now() / 1000);
      const maxAge = 300; // 5 minutes

      if (isNaN(requestTime) || now - requestTime > maxAge) {
        logger.warn(`SSO: Timestamp expiré ou invalide: ${timestamp}`);
        return false;
      }
    }

    // Construction de la chaîne à signer
    // Format: username:domain:timestamp (si timestamp présent)
    const dataToSign = timestamp
      ? `${username}:${domain}:${timestamp}`
      : `${username}:${domain}`;

    // Calcul de la signature attendue
    const expectedSignature = crypto
      .createHmac('sha256', secret)
      .update(dataToSign)
      .digest('hex');

    // Comparaison sécurisée (timing-safe)
    const signatureBuffer = Buffer.from(signature, 'hex');
    const expectedBuffer = Buffer.from(expectedSignature, 'hex');

    if (signatureBuffer.length !== expectedBuffer.length) {
      return false;
    }

    const isValid = crypto.timingSafeEqual(signatureBuffer, expectedBuffer);

    if (!isValid) {
      logger.debug(`SSO: Signature invalide pour ${username}@${domain}`);
      logger.debug(`SSO: Attendu: ${expectedSignature}`);
      logger.debug(`SSO: Reçu: ${signature}`);
    }

    return isValid;
  } catch (error) {
    logger.error(`SSO: Erreur de vérification de signature: ${error.message}`);
    return false;
  }
}

/**
 * Génère une signature SSO (pour tests ou intégration)
 * Cette fonction peut être utilisée côté HestiaCP pour générer la signature
 * 
 * @param {string} username - Nom d'utilisateur
 * @param {string} domain - Domaine
 * @param {boolean} includeTimestamp - Inclure un timestamp
 * @returns {object} Signature et paramètres
 */
function generateSignature(username, domain, includeTimestamp = true) {
  const secret = config.hestia.ssoSecret;
  const timestamp = includeTimestamp ? Math.floor(Date.now() / 1000).toString() : null;

  const dataToSign = timestamp
    ? `${username}:${domain}:${timestamp}`
    : `${username}:${domain}`;

  const signature = crypto
    .createHmac('sha256', secret)
    .update(dataToSign)
    .digest('hex');

  return {
    h_user: username,
    h_domain: domain,
    h_sig: signature,
    ...(timestamp && { h_timestamp: timestamp })
  };
}

/**
 * Génère l'URL SSO complète pour rediriger vers le builder
 * Utilisé côté HestiaCP pour créer le lien
 * 
 * @param {string} username - Nom d'utilisateur
 * @param {string} domain - Domaine
 * @param {string} builderBaseUrl - URL de base du builder
 * @returns {string} URL complète avec les paramètres SSO
 */
function generateSsoUrl(username, domain, builderBaseUrl = 'http://localhost:3001') {
  const params = generateSignature(username, domain, true);
  const queryString = new URLSearchParams(params).toString();
  
  return `${builderBaseUrl}/api/auth/sso-redirect?${queryString}`;
}

/**
 * Code PHP à intégrer dans HestiaCP pour générer les liens SSO
 * Ce code est fourni comme référence pour l'intégration
 */
const PHP_INTEGRATION_CODE = `
<?php
/**
 * Génère un lien SSO vers le Site Builder
 * À intégrer dans HestiaCP (ex: web/templates/pages/list_web.php)
 */

function generateSiteBuilderUrl(\$username, \$domain, \$secret, \$builderUrl = 'https://builder.example.com') {
    \$timestamp = time();
    \$dataToSign = "\$username:\$domain:\$timestamp";
    \$signature = hash_hmac('sha256', \$dataToSign, \$secret);
    
    \$params = http_build_query([
        'h_user' => \$username,
        'h_domain' => \$domain,
        'h_timestamp' => \$timestamp,
        'h_sig' => \$signature
    ]);
    
    return "\$builderUrl/api/auth/sso-redirect?\$params";
}

// Exemple d'utilisation dans un template HestiaCP:
// \$builderLink = generateSiteBuilderUrl(\$user, \$domain, 'votre_secret_partage');
// echo '<a href="' . \$builderLink . '" target="_blank">Ouvrir Site Builder</a>';
?>
`;

/**
 * Script bash pour tester l'intégration SSO
 */
const BASH_TEST_SCRIPT = `
#!/bin/bash
# Test de l'intégration SSO

# Configuration
SECRET="votre_secret_ici"
USERNAME="testuser"
DOMAIN="example.com"
BUILDER_URL="http://localhost:3001"

# Générer le timestamp
TIMESTAMP=$(date +%s)

# Générer la signature
DATA_TO_SIGN="\${USERNAME}:\${DOMAIN}:\${TIMESTAMP}"
SIGNATURE=$(echo -n "\$DATA_TO_SIGN" | openssl dgst -sha256 -hmac "\$SECRET" | cut -d' ' -f2)

# Construire l'URL
URL="\${BUILDER_URL}/api/auth/sso-redirect?h_user=\${USERNAME}&h_domain=\${DOMAIN}&h_timestamp=\${TIMESTAMP}&h_sig=\${SIGNATURE}"

echo "URL SSO générée:"
echo "\$URL"

# Tester avec curl (optionnel)
# curl -v "\$URL"
`;

module.exports = {
  verifySignature,
  generateSignature,
  generateSsoUrl,
  PHP_INTEGRATION_CODE,
  BASH_TEST_SCRIPT
};
