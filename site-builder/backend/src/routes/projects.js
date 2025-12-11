/**
 * ===========================================
 * Routes des projets
 * ===========================================
 * 
 * Gère les opérations CRUD sur les projets et la publication.
 * Toutes les routes sont protégées par authentification.
 * 
 * Routes:
 * - GET    /api/projects - Liste des projets de l'utilisateur
 * - POST   /api/projects - Créer un nouveau projet
 * - GET    /api/projects/:id - Détails d'un projet
 * - PUT    /api/projects/:id - Mettre à jour un projet
 * - DELETE /api/projects/:id - Supprimer un projet
 * - POST   /api/projects/:id/publish - Publier le site
 * - GET    /api/projects/:id/preview - Prévisualiser le site
 */

const express = require('express');
const router = express.Router();

const projectController = require('../controllers/projectController');
const { authenticate, authorizeProjectOwner } = require('../middleware/auth');

// Toutes les routes de projets nécessitent une authentification
router.use(authenticate);

/**
 * GET /api/projects
 * Liste tous les projets de l'utilisateur connecté
 * 
 * Réponse:
 * {
 *   success: true,
 *   data: {
 *     projects: [{ id, domain_name, project_name, ... }]
 *   }
 * }
 */
router.get('/', projectController.listProjects);

/**
 * POST /api/projects
 * Crée un nouveau projet
 * 
 * Body:
 * - domain_name: string (requis) - Nom de domaine
 * - project_name: string (optionnel) - Nom affiché du projet
 * - settings: object (optionnel) - Paramètres (couleurs, fonts, etc.)
 * 
 * Réponse:
 * {
 *   success: true,
 *   message: 'Projet créé avec succès',
 *   data: { project: {...} }
 * }
 */
router.post('/', projectController.createProject);

/**
 * GET /api/projects/:id
 * Récupère les détails complets d'un projet avec ses pages
 * 
 * Middleware: authorizeProjectOwner vérifie que l'utilisateur
 * est bien le propriétaire du projet
 */
router.get('/:id', authorizeProjectOwner, projectController.getProject);

/**
 * PUT /api/projects/:id
 * Met à jour les informations d'un projet
 * 
 * Body (tous optionnels):
 * - project_name: string - Nouveau nom
 * - settings: object - Paramètres mis à jour
 * - thumbnail: string - Aperçu en base64 ou URL
 * - publish_path: string - Chemin de publication personnalisé
 */
router.put('/:id', authorizeProjectOwner, projectController.updateProject);

/**
 * DELETE /api/projects/:id
 * Supprime un projet et toutes ses pages
 * Action irréversible !
 */
router.delete('/:id', authorizeProjectOwner, projectController.deleteProject);

/**
 * POST /api/projects/:id/publish
 * Publie le site en générant les fichiers statiques
 * 
 * Le service de publication :
 * 1. Génère le HTML pour chaque page
 * 2. Génère le CSS global
 * 3. Génère le JS global
 * 4. Écrit les fichiers dans le dossier de publication
 * 
 * Réponse:
 * {
 *   success: true,
 *   message: 'Site publié avec succès',
 *   data: {
 *     publishedAt: '2024-...',
 *     publishPath: '/home/user/web/domain.com/public_html',
 *     files: ['index.html', 'about.html', 'style.css', 'app.js']
 *   }
 * }
 */
router.post('/:id/publish', authorizeProjectOwner, projectController.publishProject);

/**
 * GET /api/projects/:id/preview
 * Génère un aperçu HTML d'une page sans la publier
 * Utile pour la prévisualisation dans le builder
 * 
 * Query:
 * - page: string - Slug de la page à prévisualiser (défaut: 'index')
 */
router.get('/:id/preview', authorizeProjectOwner, projectController.previewProject);

module.exports = router;
