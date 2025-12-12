/**
 * ===========================================
 * Routes des Assets (Media Manager)
 * ===========================================
 * 
 * Routes:
 * - GET    /api/projects/:projectId/assets - Liste des assets
 * - POST   /api/projects/:projectId/assets - Upload fichier(s)
 * - PUT    /api/projects/:projectId/assets/:assetId - Mise à jour
 * - DELETE /api/projects/:projectId/assets/:assetId - Suppression
 * - GET    /api/projects/:projectId/assets/folders - Liste des dossiers
 */

const express = require('express');
const router = express.Router({ mergeParams: true });
const multer = require('multer');

const assetController = require('../controllers/assetController');
const { authenticate, authorizeProjectOwner } = require('../middleware/auth');

// Configuration multer pour l'upload en mémoire
const upload = multer({
  storage: multer.memoryStorage(),
  limits: {
    fileSize: 10 * 1024 * 1024, // 10MB max
    files: 10 // 10 fichiers max par requête
  }
});

// Toutes les routes nécessitent authentification + être propriétaire du projet
router.use(authenticate);

/**
 * GET /api/projects/:projectId/assets
 * Liste tous les assets du projet
 * Query params: folder, type, limit, offset
 */
router.get('/', authorizeProjectOwner, assetController.listAssets);

/**
 * GET /api/projects/:projectId/assets/folders
 * Liste les dossiers existants
 */
router.get('/folders', authorizeProjectOwner, assetController.listFolders);

/**
 * POST /api/projects/:projectId/assets
 * Upload un ou plusieurs fichiers
 * Body (multipart/form-data):
 * - files: fichier(s) à uploader
 * - folder: dossier de destination (optionnel)
 * - alt_text: texte alternatif (optionnel)
 * - tags: tags séparés par virgules (optionnel)
 */
router.post('/', authorizeProjectOwner, upload.array('files', 10), assetController.uploadAsset);

/**
 * PUT /api/projects/:projectId/assets/:assetId
 * Met à jour les métadonnées d'un asset
 */
router.put('/:assetId', authorizeProjectOwner, assetController.updateAsset);

/**
 * DELETE /api/projects/:projectId/assets/:assetId
 * Supprime un asset
 */
router.delete('/:assetId', authorizeProjectOwner, assetController.deleteAsset);

module.exports = router;
