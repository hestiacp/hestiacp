/**
 * ===========================================
 * Contrôleur d'authentification
 * ===========================================
 * 
 * Gère l'authentification SSO depuis HestiaCP
 * et les opérations liées à l'authentification.
 */

const { User, Project } = require('../models');
const { generateToken } = require('../middleware/auth');
const ssoService = require('../services/ssoService');
const logger = require('../config/logger');
const { ApiError, asyncHandler } = require('../middleware/errorHandler');

/**
 * POST /api/auth/sso
 * Authentification SSO depuis HestiaCP
 * 
 * Paramètres attendus (query ou body):
 * - h_user: Nom d'utilisateur HestiaCP
 * - h_domain: Domaine associé
 * - h_sig: Signature de vérification
 * - h_timestamp: Timestamp de la requête (optionnel, pour éviter le replay)
 */
const ssoLogin = asyncHandler(async (req, res) => {
  // Les paramètres peuvent venir de query (GET) ou body (POST)
  const params = { ...req.query, ...req.body };
  const { h_user, h_domain, h_sig, h_timestamp } = params;

  // Validation des paramètres requis
  if (!h_user || !h_domain || !h_sig) {
    throw new ApiError(400, 'Paramètres SSO manquants (h_user, h_domain, h_sig requis)');
  }

  // Vérification de la signature SSO
  const isValid = ssoService.verifySignature({
    username: h_user,
    domain: h_domain,
    signature: h_sig,
    timestamp: h_timestamp
  });

  if (!isValid) {
    logger.warn(`Tentative SSO échouée pour ${h_user}@${h_domain}`);
    throw new ApiError(401, 'Signature SSO invalide');
  }

  // Trouver ou créer l'utilisateur
  const { user, created: userCreated } = await User.findOrCreateByHestiaUsername(h_user);
  
  if (userCreated) {
    logger.info(`Nouvel utilisateur créé via SSO: ${h_user}`);
  }

  // Trouver ou créer le projet pour ce domaine
  const { project, created: projectCreated } = await Project.findOrCreateByDomain(
    user.id,
    h_domain,
    h_domain // Nom du projet = nom de domaine par défaut
  );

  if (projectCreated) {
    logger.info(`Nouveau projet créé: ${h_domain} pour ${h_user}`);
  }

  // Générer le token JWT
  const token = generateToken(user);

  // Réponse avec le token et les infos
  res.json({
    success: true,
    message: 'Authentification SSO réussie',
    data: {
      token,
      user: user.toSafeJSON(),
      project: {
        id: project.id,
        domain_name: project.domain_name,
        project_name: project.project_name
      },
      // URL de redirection vers le builder
      redirectUrl: `/builder/${project.id}`
    }
  });
});

/**
 * GET /api/auth/sso-redirect
 * Point d'entrée SSO avec redirection vers le frontend
 * Utilisé quand HestiaCP redirige directement l'utilisateur
 */
const ssoRedirect = asyncHandler(async (req, res) => {
  const { h_user, h_domain, h_sig, h_timestamp } = req.query;

  // Validation des paramètres requis
  if (!h_user || !h_domain || !h_sig) {
    return res.redirect(
      `${process.env.FRONTEND_URL}/error?message=Paramètres SSO manquants`
    );
  }

  // Vérification de la signature SSO
  const isValid = ssoService.verifySignature({
    username: h_user,
    domain: h_domain,
    signature: h_sig,
    timestamp: h_timestamp
  });

  if (!isValid) {
    logger.warn(`Tentative SSO échouée pour ${h_user}@${h_domain}`);
    return res.redirect(
      `${process.env.FRONTEND_URL}/error?message=Signature SSO invalide`
    );
  }

  // Trouver ou créer l'utilisateur et le projet
  const { user } = await User.findOrCreateByHestiaUsername(h_user);
  const { project } = await Project.findOrCreateByDomain(user.id, h_domain);

  // Générer le token JWT
  const token = generateToken(user);

  // Rediriger vers le frontend avec le token
  const frontendUrl = process.env.FRONTEND_URL || 'http://localhost:5173';
  res.redirect(`${frontendUrl}/auth/callback?token=${token}&projectId=${project.id}`);
});

/**
 * GET /api/auth/me
 * Récupère les informations de l'utilisateur connecté
 */
const getCurrentUser = asyncHandler(async (req, res) => {
  const user = await User.findByPk(req.userId, {
    include: [{
      association: 'projects',
      attributes: ['id', 'domain_name', 'project_name', 'is_published', 'updated_at']
    }]
  });

  if (!user) {
    throw new ApiError(404, 'Utilisateur non trouvé');
  }

  res.json({
    success: true,
    data: {
      user: user.toSafeJSON(),
      projects: user.projects
    }
  });
});

/**
 * POST /api/auth/logout
 * Déconnexion (côté client, invalider le token)
 */
const logout = asyncHandler(async (req, res) => {
  // Avec JWT, la déconnexion est principalement gérée côté client
  // En production, on pourrait maintenir une blacklist de tokens
  
  res.json({
    success: true,
    message: 'Déconnexion réussie'
  });
});

/**
 * POST /api/auth/refresh
 * Rafraîchit le token JWT
 */
const refreshToken = asyncHandler(async (req, res) => {
  const user = await User.findByPk(req.userId);

  if (!user || !user.is_active) {
    throw new ApiError(401, 'Utilisateur invalide ou inactif');
  }

  const token = generateToken(user);

  res.json({
    success: true,
    data: { token }
  });
});

module.exports = {
  ssoLogin,
  ssoRedirect,
  getCurrentUser,
  logout,
  refreshToken
};
