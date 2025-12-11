<?php
/**
 * ===========================================
 * HestiaCP Site Builder - Fonctions d'intégration
 * ===========================================
 * 
 * Ce fichier doit être copié dans :
 *   /usr/local/hestia/web/inc/sitebuilder.php
 * 
 * Et inclus dans list_web.php avec :
 *   require_once('/usr/local/hestia/web/inc/sitebuilder.php');
 */

// ============================================
// CONFIGURATION - À MODIFIER SELON VOTRE SETUP
// ============================================

// URL du Site Builder (sans slash final)
define('SITEBUILDER_URL', 'https://builder.votre-domaine.com');

// Secret SSO partagé - DOIT ÊTRE IDENTIQUE à HESTIA_SSO_SECRET dans le .env du backend
define('SITEBUILDER_SSO_SECRET', 'votre_secret_sso_ici');

// ============================================
// FONCTIONS - NE PAS MODIFIER
// ============================================

/**
 * Génère l'URL SSO sécurisée pour accéder au Site Builder
 * 
 * @param string $username - Nom d'utilisateur HestiaCP (ex: admin)
 * @param string $domain - Nom de domaine (ex: example.com)
 * @return string URL complète avec signature
 */
function sitebuilder_url($username, $domain) {
    $timestamp = time();
    
    // Chaîne à signer : username:domain:timestamp
    $data_to_sign = "{$username}:{$domain}:{$timestamp}";
    
    // Signature HMAC-SHA256
    $signature = hash_hmac('sha256', $data_to_sign, SITEBUILDER_SSO_SECRET);
    
    // Construction de l'URL
    $params = http_build_query([
        'h_user' => $username,
        'h_domain' => $domain,
        'h_timestamp' => $timestamp,
        'h_sig' => $signature
    ]);
    
    return SITEBUILDER_URL . '/api/auth/sso-redirect?' . $params;
}

/**
 * Vérifie si le Site Builder est activé pour un domaine
 * (Extension future pour gérer les licences ou restrictions)
 * 
 * @param string $username
 * @param string $domain
 * @return bool
 */
function sitebuilder_enabled($username, $domain) {
    // Pour l'instant, toujours activé
    // Vous pouvez ajouter des conditions ici :
    // - Vérifier un fichier de config
    // - Vérifier une licence
    // - Vérifier un plan utilisateur
    return true;
}
