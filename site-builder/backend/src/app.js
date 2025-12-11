/**
 * ===========================================
 * HestiaCP Site Builder - Configuration Express
 * ===========================================
 * 
 * Configuration principale de l'application Express.
 * Middlewares, routes et gestion des erreurs.
 */

const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const session = require('express-session');

const routes = require('./routes');
const errorHandler = require('./middleware/errorHandler');
const logger = require('./config/logger');

const app = express();

// ===========================================
// MIDDLEWARES DE SÉCURITÉ
// ===========================================

// Helmet - Headers de sécurité HTTP
app.use(helmet({
  crossOriginResourcePolicy: { policy: "cross-origin" }
}));

// CORS - Configuration pour le frontend
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:5173',
  credentials: true,
  methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));

// Rate Limiting - Protection contre les attaques par force brute
const limiter = rateLimit({
  windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS) || 60000, // 1 minute
  max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS) || 100, // 100 requêtes par minute
  message: {
    success: false,
    message: 'Trop de requêtes, veuillez réessayer plus tard.'
  },
  standardHeaders: true,
  legacyHeaders: false
});
app.use('/api', limiter);

// ===========================================
// MIDDLEWARES DE PARSING
// ===========================================

// Parser JSON avec une limite de taille élevée pour les données GrapesJS
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// ===========================================
// SESSIONS
// ===========================================

app.use(session({
  secret: process.env.JWT_SECRET || 'dev-secret-change-in-production',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: process.env.SECURE_COOKIES === 'true',
    httpOnly: true,
    maxAge: 7 * 24 * 60 * 60 * 1000 // 7 jours
  }
}));

// ===========================================
// LOGGING DES REQUÊTES
// ===========================================

app.use((req, res, next) => {
  const start = Date.now();
  res.on('finish', () => {
    const duration = Date.now() - start;
    logger.http(`${req.method} ${req.originalUrl} ${res.statusCode} - ${duration}ms`);
  });
  next();
});

// ===========================================
// ROUTES
// ===========================================

// Route de santé pour les health checks
app.get('/health', (req, res) => {
  res.json({
    success: true,
    message: 'Site Builder API is running',
    timestamp: new Date().toISOString(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Routes API principales
app.use('/api', routes);

// ===========================================
// GESTION DES ERREURS
// ===========================================

// Route 404 pour les routes non trouvées
app.use((req, res, next) => {
  res.status(404).json({
    success: false,
    message: `Route non trouvée: ${req.method} ${req.originalUrl}`
  });
});

// Middleware de gestion des erreurs globales
app.use(errorHandler);

module.exports = app;
