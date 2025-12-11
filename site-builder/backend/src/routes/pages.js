/**
 * ===========================================
 * Routes des pages
 * ===========================================
 * 
 * Gère les opérations CRUD sur les pages d'un projet.
 * Routes imbriquées sous /api/projects/:projectId/pages
 * 
 * Routes:
 * - GET    /api/projects/:projectId/pages - Liste des pages
 * - POST   /api/projects/:projectId/pages - Créer une page
 * - GET    /api/projects/:projectId/pages/:pageId - Détails d'une page
 * - PUT    /api/projects/:projectId/pages/:pageId - Mettre à jour une page
 * - DELETE /api/projects/:projectId/pages/:pageId - Supprimer une page
 * - PUT    /api/projects/:projectId/pages/reorder - Réordonner les pages
 * - POST   /api/projects/:projectId/pages/:pageId/duplicate - Dupliquer une page
 */

const express = require('express');
const router = express.Router();

const pageController = require('../controllers/pageController');
const { authenticate, authorizeProjectOwner } = require('../middleware/auth');

// Toutes les routes nécessitent une authentification
router.use(authenticate);

// Middleware pour vérifier la propriété du projet
// Appliqué à toutes les routes avec :projectId
router.param('projectId', async (req, res, next, projectId) => {
  req.params.id = projectId; // Pour le middleware authorizeProjectOwner
  await authorizeProjectOwner(req, res, next);
});

/**
 * GET /api/projects/:projectId/pages
 * Liste toutes les pages d'un projet, ordonnées
 * 
 * Réponse:
 * {
 *   success: true,
 *   data: {
 *     pages: [{ id, name, slug, is_homepage, order_index, ... }]
 *   }
 * }
 */
router.get('/:projectId/pages', pageController.listPages);

/**
 * PUT /api/projects/:projectId/pages/reorder
 * Réordonne les pages d'un projet
 * ATTENTION: Cette route doit être avant /:projectId/pages/:pageId
 * 
 * Body:
 * - pages: [{ id: string, order_index: number }]
 */
router.put('/:projectId/pages/reorder', pageController.reorderPages);

/**
 * POST /api/projects/:projectId/pages
 * Crée une nouvelle page dans le projet
 * 
 * Body:
 * - name: string (requis) - Nom de la page
 * - slug: string (optionnel) - Slug URL, généré depuis le nom si absent
 * - grapesjs_data: object (optionnel) - Données GrapesJS initiales
 * - is_homepage: boolean (optionnel) - Définir comme page d'accueil
 * - seo: object (optionnel) - Métadonnées SEO
 */
router.post('/:projectId/pages', pageController.createPage);

/**
 * GET /api/projects/:projectId/pages/:pageId
 * Récupère les détails d'une page avec ses données GrapesJS
 * 
 * Cette route est utilisée par le frontend pour charger
 * les données complètes d'une page dans l'éditeur GrapesJS
 */
router.get('/:projectId/pages/:pageId', pageController.getPage);

/**
 * PUT /api/projects/:projectId/pages/:pageId
 * Met à jour une page (données GrapesJS, nom, slug, etc.)
 * 
 * Body (tous optionnels):
 * - name: string - Nouveau nom
 * - slug: string - Nouveau slug
 * - grapesjs_data: object - Nouvelles données GrapesJS
 *   - components: array - Composants de la page
 *   - styles: array - Styles CSS
 *   - assets: array - Assets (images, etc.)
 * - is_homepage: boolean - Définir comme page d'accueil
 * - seo: object - Métadonnées SEO
 * - order_index: number - Nouvel ordre
 */
router.put('/:projectId/pages/:pageId', pageController.updatePage);

/**
 * DELETE /api/projects/:projectId/pages/:pageId
 * Supprime une page
 * 
 * Note: Impossible de supprimer la dernière page d'un projet
 * Si la page supprimée est la homepage, une autre page
 * sera automatiquement promue
 */
router.delete('/:projectId/pages/:pageId', pageController.deletePage);

/**
 * POST /api/projects/:projectId/pages/:pageId/duplicate
 * Duplique une page avec un nouveau slug
 * 
 * Réponse:
 * {
 *   success: true,
 *   message: 'Page dupliquée avec succès',
 *   data: { page: {...} }
 * }
 */
router.post('/:projectId/pages/:pageId/duplicate', pageController.duplicatePage);

module.exports = router;
