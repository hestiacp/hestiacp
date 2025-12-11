/**
 * ===========================================
 * Index des routes API
 * ===========================================
 * 
 * Point d'entrée central pour toutes les routes de l'API.
 * Organise et monte les différents routeurs.
 */

const express = require('express');
const router = express.Router();

const authRoutes = require('./auth');
const projectRoutes = require('./projects');
const pageRoutes = require('./pages');

// Route de bienvenue de l'API
router.get('/', (req, res) => {
  res.json({
    success: true,
    message: 'HestiaCP Site Builder API',
    version: '1.0.0',
    endpoints: {
      auth: '/api/auth',
      projects: '/api/projects',
      pages: '/api/projects/:projectId/pages',
      health: '/health'
    },
    documentation: 'https://github.com/hestiacp/site-builder'
  });
});

// Monter les routes
router.use('/auth', authRoutes);
router.use('/projects', projectRoutes);
router.use('/projects', pageRoutes); // Les routes de pages sont imbriquées sous /projects

module.exports = router;
