/**
 * ===========================================
 * Routes d'authentification
 * ===========================================
 * 
 * Gère les routes liées à l'authentification SSO et JWT.
 * 
 * Routes:
 * - POST /api/auth/sso - Authentification SSO (API)
 * - GET  /api/auth/sso-redirect - Authentification SSO avec redirection
 * - GET  /api/auth/me - Informations utilisateur connecté
 * - POST /api/auth/logout - Déconnexion
 * - POST /api/auth/refresh - Rafraîchir le token
 */

const express = require('express');
const router = express.Router();

const authController = require('../controllers/authController');
const { authenticate } = require('../middleware/auth');

// ===========================================
// ROUTES PUBLIQUES (pas d'authentification requise)
// ===========================================

/**
 * POST /api/auth/sso
 * Authentification SSO via API (retourne JSON)
 * 
 * Body/Query:
 * - h_user: string (requis) - Nom d'utilisateur HestiaCP
 * - h_domain: string (requis) - Domaine associé
 * - h_sig: string (requis) - Signature HMAC
 * - h_timestamp: string (optionnel) - Timestamp de la requête
 */
router.post('/sso', authController.ssoLogin);

/**
 * GET /api/auth/sso-redirect
 * Authentification SSO avec redirection vers le frontend
 * Appelé directement depuis HestiaCP (lien dans l'interface)
 * 
 * Query:
 * - h_user: string (requis)
 * - h_domain: string (requis)
 * - h_sig: string (requis)
 * - h_timestamp: string (optionnel)
 */
router.get('/sso-redirect', authController.ssoRedirect);

// ===========================================
// ROUTES PROTÉGÉES (authentification requise)
// ===========================================

/**
 * GET /api/auth/me
 * Récupère les informations de l'utilisateur connecté
 * et ses projets
 * 
 * Headers:
 * - Authorization: Bearer <token>
 */
router.get('/me', authenticate, authController.getCurrentUser);

/**
 * POST /api/auth/logout
 * Déconnexion de l'utilisateur
 * Côté serveur, on peut implémenter une blacklist de tokens
 */
router.post('/logout', authenticate, authController.logout);

/**
 * POST /api/auth/refresh
 * Génère un nouveau token JWT
 * Utile pour prolonger la session sans re-authentification
 */
router.post('/refresh', authenticate, authController.refreshToken);

module.exports = router;
