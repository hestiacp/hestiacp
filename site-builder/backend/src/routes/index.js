/**
 * ===========================================
 * Index des routes API
 * ===========================================
 * 
 * Point d'entrée central pour toutes les routes de l'API.
 */

const express = require('express');
const router = express.Router();

const authRoutes = require('./auth');
const projectRoutes = require('./projects');
const pageRoutes = require('./pages');
const assetRoutes = require('./assets');
const { publicRouter: formPublicRoutes, adminRouter: formAdminRoutes } = require('./forms');

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
      assets: '/api/projects/:projectId/assets',
      forms: '/api/forms',
      health: '/health'
    },
    documentation: 'https://github.com/hestiacp/site-builder'
  });
});

// Monter les routes
router.use('/auth', authRoutes);
router.use('/projects', projectRoutes);
router.use('/projects', pageRoutes);
router.use('/projects/:projectId/assets', assetRoutes);
router.use('/projects/:projectId/forms', formAdminRoutes);
router.use('/forms', formPublicRoutes);

module.exports = router;
