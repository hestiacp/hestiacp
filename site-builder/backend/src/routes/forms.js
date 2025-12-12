/**
 * ===========================================
 * Routes des Formulaires
 * ===========================================
 * 
 * Routes publiques:
 * - POST /api/forms/submit - Soumettre un formulaire
 * 
 * Routes protégées (admin):
 * - GET /api/projects/:projectId/forms/submissions
 * - GET /api/projects/:projectId/forms/submissions/:submissionId
 * - PUT /api/projects/:projectId/forms/submissions/:submissionId
 * - DELETE /api/projects/:projectId/forms/submissions/:submissionId
 * - GET /api/projects/:projectId/forms/count
 */

const express = require('express');
const router = express.Router();

const formController = require('../controllers/formController');
const { authenticate, authorizeProjectOwner } = require('../middleware/auth');

// ===========================================
// ROUTE PUBLIQUE (soumission de formulaire)
// ===========================================

/**
 * POST /api/forms/submit
 * Endpoint public pour recevoir les soumissions de formulaires
 * 
 * Body:
 * - project_id: string (requis)
 * - form_id: string (optionnel)
 * - form_name: string (optionnel)
 * - page_slug: string (optionnel)
 * - data: object (les champs du formulaire)
 */
router.post('/submit', formController.submitForm);

module.exports = router;


// ===========================================
// ROUTES ADMIN (séparées, montées sous /projects/:projectId)
// ===========================================

const adminRouter = express.Router({ mergeParams: true });

// Toutes les routes admin nécessitent authentification
adminRouter.use(authenticate);

/**
 * GET /api/projects/:projectId/forms/submissions
 * Liste les soumissions du projet
 */
adminRouter.get('/submissions', authorizeProjectOwner, formController.listSubmissions);

/**
 * GET /api/projects/:projectId/forms/count
 * Compte les nouvelles soumissions
 */
adminRouter.get('/count', authorizeProjectOwner, formController.countNew);

/**
 * GET /api/projects/:projectId/forms/submissions/:submissionId
 * Détails d'une soumission
 */
adminRouter.get('/submissions/:submissionId', authorizeProjectOwner, formController.getSubmission);

/**
 * PUT /api/projects/:projectId/forms/submissions/:submissionId
 * Met à jour une soumission (statut, notes)
 */
adminRouter.put('/submissions/:submissionId', authorizeProjectOwner, formController.updateSubmission);

/**
 * DELETE /api/projects/:projectId/forms/submissions/:submissionId
 * Supprime une soumission
 */
adminRouter.delete('/submissions/:submissionId', authorizeProjectOwner, formController.deleteSubmission);

module.exports.publicRouter = router;
module.exports.adminRouter = adminRouter;
