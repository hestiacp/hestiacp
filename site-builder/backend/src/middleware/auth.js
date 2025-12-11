/**
 * ===========================================
 * Middleware d'authentification
 * ===========================================
 * 
 * Gère l'authentification JWT et la vérification des permissions.
 * Utilisé pour protéger les routes API.
 */

const jwt = require('jsonwebtoken');
const config = require('../config');
const { User } = require('../models');
const logger = require('../config/logger');

/**
 * Middleware de vérification du token JWT
 * Extrait et valide le token depuis le header Authorization
 * Ajoute l'utilisateur à req.user si valide
 */
const authenticate = async (req, res, next) => {
  try {
    // Récupérer le token depuis le header Authorization
    const authHeader = req.headers.authorization;
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Token d\'authentification manquant'
      });
    }

    const token = authHeader.split(' ')[1];

    // Vérifier et décoder le token
    const decoded = jwt.verify(token, config.jwt.secret);

    // Récupérer l'utilisateur depuis la base de données
    const user = await User.findByPk(decoded.userId);

    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Utilisateur non trouvé'
      });
    }

    if (!user.is_active) {
      return res.status(403).json({
        success: false,
        message: 'Compte désactivé'
      });
    }

    // Ajouter l'utilisateur à la requête
    req.user = user;
    req.userId = user.id;

    next();
  } catch (error) {
    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Token expiré, veuillez vous reconnecter'
      });
    }

    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        success: false,
        message: 'Token invalide'
      });
    }

    logger.error('Erreur d\'authentification:', error);
    return res.status(500).json({
      success: false,
      message: 'Erreur d\'authentification'
    });
  }
};

/**
 * Middleware de vérification des rôles
 * @param {string[]} roles - Liste des rôles autorisés
 */
const authorize = (...roles) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: 'Non authentifié'
      });
    }

    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: 'Accès non autorisé pour ce rôle'
      });
    }

    next();
  };
};

/**
 * Middleware de vérification de propriété d'un projet
 * Vérifie que l'utilisateur est bien le propriétaire du projet
 */
const authorizeProjectOwner = async (req, res, next) => {
  try {
    const { Project } = require('../models');
    const projectId = req.params.id || req.params.projectId;

    if (!projectId) {
      return res.status(400).json({
        success: false,
        message: 'ID de projet manquant'
      });
    }

    const project = await Project.findByPk(projectId);

    if (!project) {
      return res.status(404).json({
        success: false,
        message: 'Projet non trouvé'
      });
    }

    // Vérifier que l'utilisateur est le propriétaire ou admin
    if (project.user_id !== req.userId && req.user.role !== 'admin') {
      return res.status(403).json({
        success: false,
        message: 'Vous n\'êtes pas autorisé à accéder à ce projet'
      });
    }

    // Ajouter le projet à la requête pour éviter une nouvelle requête
    req.project = project;

    next();
  } catch (error) {
    logger.error('Erreur de vérification de propriété:', error);
    return res.status(500).json({
      success: false,
      message: 'Erreur de vérification des permissions'
    });
  }
};

/**
 * Génère un token JWT pour un utilisateur
 * @param {User} user - Instance de l'utilisateur
 * @returns {string} Token JWT
 */
const generateToken = (user) => {
  return jwt.sign(
    {
      userId: user.id,
      hestiaUsername: user.hestia_username,
      role: user.role
    },
    config.jwt.secret,
    { expiresIn: config.jwt.expiresIn }
  );
};

module.exports = {
  authenticate,
  authorize,
  authorizeProjectOwner,
  generateToken
};
