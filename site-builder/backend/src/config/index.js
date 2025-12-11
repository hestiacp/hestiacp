/**
 * ===========================================
 * Configuration centrale de l'application
 * ===========================================
 * 
 * Centralise toutes les configurations de l'application.
 * Utilise les variables d'environnement avec des valeurs par défaut.
 */

module.exports = {
  // Configuration du serveur
  server: {
    port: parseInt(process.env.PORT) || 3001,
    env: process.env.NODE_ENV || 'development',
    frontendUrl: process.env.FRONTEND_URL || 'http://localhost:5173'
  },

  // Configuration de la base de données
  database: {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT) || 5432,
    name: process.env.DB_NAME || 'site_builder',
    user: process.env.DB_USER || 'site_builder_user',
    password: process.env.DB_PASSWORD || 'password',
    dialect: 'postgres',
    logging: process.env.NODE_ENV === 'development',
    pool: {
      max: 5,
      min: 0,
      acquire: 30000,
      idle: 10000
    }
  },

  // Configuration JWT
  jwt: {
    secret: process.env.JWT_SECRET || 'dev-secret-change-in-production',
    expiresIn: process.env.JWT_EXPIRES_IN || '7d'
  },

  // Configuration SSO HestiaCP
  hestia: {
    ssoSecret: process.env.HESTIA_SSO_SECRET || 'shared-secret',
    // Template du chemin de publication
    // {USERNAME} et {DOMAIN} seront remplacés
    publishBasePath: process.env.PUBLISH_BASE_PATH || '/home/{USERNAME}/web/{DOMAIN}/public_html'
  },

  // Configuration de sécurité
  security: {
    secureCookies: process.env.SECURE_COOKIES === 'true',
    rateLimitWindowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 60000,
    rateLimitMaxRequests: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100
  },

  // Configuration des logs
  logging: {
    level: process.env.LOG_LEVEL || 'debug',
    file: process.env.LOG_FILE || './logs/app.log'
  }
};
