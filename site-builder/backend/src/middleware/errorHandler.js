/**
 * ===========================================
 * Middleware de gestion des erreurs
 * ===========================================
 * 
 * Gestion centralisée des erreurs de l'application.
 * Formate les erreurs de manière cohérente pour l'API.
 */

const logger = require('../config/logger');

/**
 * Classe d'erreur personnalisée pour l'API
 * Permet de créer des erreurs avec un code HTTP spécifique
 */
class ApiError extends Error {
  constructor(statusCode, message, details = null) {
    super(message);
    this.statusCode = statusCode;
    this.details = details;
    this.isOperational = true;

    Error.captureStackTrace(this, this.constructor);
  }
}

/**
 * Middleware de gestion des erreurs
 * Intercepte toutes les erreurs et les formate pour l'API
 */
const errorHandler = (err, req, res, next) => {
  // Log de l'erreur
  logger.error(`Error: ${err.message}`, {
    stack: err.stack,
    path: req.path,
    method: req.method,
    body: req.body,
    user: req.user?.id
  });

  // Erreur Sequelize - Validation
  if (err.name === 'SequelizeValidationError') {
    const errors = err.errors.map(e => ({
      field: e.path,
      message: e.message
    }));

    return res.status(400).json({
      success: false,
      message: 'Erreur de validation',
      errors
    });
  }

  // Erreur Sequelize - Contrainte unique
  if (err.name === 'SequelizeUniqueConstraintError') {
    const field = err.errors[0]?.path || 'champ';
    return res.status(409).json({
      success: false,
      message: `Cette valeur existe déjà pour le champ: ${field}`
    });
  }

  // Erreur Sequelize - Clé étrangère
  if (err.name === 'SequelizeForeignKeyConstraintError') {
    return res.status(400).json({
      success: false,
      message: 'Référence invalide vers une ressource inexistante'
    });
  }

  // Erreur Sequelize - Connexion base de données
  if (err.name === 'SequelizeConnectionError') {
    return res.status(503).json({
      success: false,
      message: 'Service temporairement indisponible'
    });
  }

  // Erreur JSON invalide
  if (err.type === 'entity.parse.failed') {
    return res.status(400).json({
      success: false,
      message: 'JSON invalide dans le corps de la requête'
    });
  }

  // Erreur de payload trop volumineux
  if (err.type === 'entity.too.large') {
    return res.status(413).json({
      success: false,
      message: 'Données trop volumineuses'
    });
  }

  // Erreur API personnalisée
  if (err instanceof ApiError) {
    return res.status(err.statusCode).json({
      success: false,
      message: err.message,
      details: err.details
    });
  }

  // Erreur par défaut (500)
  const statusCode = err.statusCode || 500;
  const message = process.env.NODE_ENV === 'production' 
    ? 'Une erreur interne est survenue'
    : err.message;

  res.status(statusCode).json({
    success: false,
    message,
    ...(process.env.NODE_ENV !== 'production' && { stack: err.stack })
  });
};

/**
 * Wrapper pour les fonctions async dans les routes
 * Évite d'avoir à mettre try/catch partout
 * 
 * @param {Function} fn - Fonction async du contrôleur
 * @returns {Function} Middleware Express
 */
const asyncHandler = (fn) => {
  return (req, res, next) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
};

module.exports = errorHandler;
module.exports.ApiError = ApiError;
module.exports.asyncHandler = asyncHandler;
