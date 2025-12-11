<?php
/**
 * ===========================================
 * Intégration HestiaCP - Site Builder
 * ===========================================
 * 
 * Ce fichier contient les fonctions nécessaires pour
 * intégrer le bouton Site Builder dans HestiaCP.
 * 
 * INSTALLATION:
 * 1. Copier ce fichier dans /usr/local/hestia/web/inc/
 * 2. Inclure dans list_web.php
 * 3. Configurer le secret partagé
 */

// Configuration - À PERSONNALISER
define('SITEBUILDER_URL', 'https://builder.votre-domaine.com');
define('SITEBUILDER_SSO_SECRET', 'votre_secret_sso_securise');

/**
 * Génère une URL SSO sécurisée pour le Site Builder
 * 
 * @param string $username - Nom d'utilisateur HestiaCP
 * @param string $domain - Nom de domaine
 * @return string URL complète avec signature
 */
function sitebuilder_generate_url($username, $domain) {
    $timestamp = time();
    
    // Construction de la chaîne à signer
    $dataToSign = "{$username}:{$domain}:{$timestamp}";
    
    // Génération de la signature HMAC-SHA256
    $signature = hash_hmac('sha256', $dataToSign, SITEBUILDER_SSO_SECRET);
    
    // Construction des paramètres
    $params = http_build_query([
        'h_user' => $username,
        'h_domain' => $domain,
        'h_timestamp' => $timestamp,
        'h_sig' => $signature
    ]);
    
    return SITEBUILDER_URL . '/api/auth/sso-redirect?' . $params;
}

/**
 * Génère le bouton HTML pour le Site Builder
 * 
 * @param string $username - Nom d'utilisateur HestiaCP
 * @param string $domain - Nom de domaine
 * @return string HTML du bouton
 */
function sitebuilder_button($username, $domain) {
    $url = sitebuilder_generate_url($username, $domain);
    
    return '
    <a href="' . htmlspecialchars($url) . '" 
       target="_blank" 
       class="button button-secondary button-small"
       title="Ouvrir le Site Builder pour éditer ce site">
        <i class="fas fa-paint-brush icon-green"></i>
        Site Builder
    </a>';
}

/**
 * Vérifie si le Site Builder est activé pour un domaine
 * (Extension future - pour gérer les licences ou restrictions)
 * 
 * @param string $username
 * @param string $domain
 * @return bool
 */
function sitebuilder_is_enabled($username, $domain) {
    // Pour l'instant, toujours activé
    // Extension future: vérifier une config ou licence
    return true;
}

/*
 * EXEMPLE D'UTILISATION DANS list_web.php
 * ========================================
 * 
 * 1. Au début du fichier, inclure ce fichier:
 *    require_once('/usr/local/hestia/web/inc/sitebuilder-button.php');
 * 
 * 2. Dans la boucle des domaines, ajouter le bouton:
 *    
 *    // Après les autres boutons d'action (edit, delete, etc.)
 *    <?php if (sitebuilder_is_enabled($user, $data[$key]['DOMAIN'])): ?>
 *        <?= sitebuilder_button($user, $data[$key]['DOMAIN']) ?>
 *    <?php endif; ?>
 * 
 * 3. Style CSS optionnel à ajouter:
 *    
 *    .icon-green { color: #10b981; }
 *    
 *    .button-small {
 *        padding: 4px 12px;
 *        font-size: 0.875rem;
 *    }
 */

// Test rapide (à exécuter en CLI pour vérifier)
// php -r "require 'sitebuilder-button.php'; echo sitebuilder_generate_url('testuser', 'example.com');"
