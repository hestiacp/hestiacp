/**
 * ===========================================
 * Contrôleur des Assets (Media Manager)
 * ===========================================
 */

const { Asset, Project } = require('../models');
const logger = require('../config/logger');
const path = require('path');
const fs = require('fs').promises;
const crypto = require('crypto');

// Dossier de stockage des uploads
const UPLOAD_DIR = process.env.UPLOAD_DIR || '/var/www/site-builder/uploads';

// Types MIME autorisés
const ALLOWED_TYPES = {
  'image/jpeg': ['.jpg', '.jpeg'],
  'image/png': ['.png'],
  'image/gif': ['.gif'],
  'image/webp': ['.webp'],
  'image/svg+xml': ['.svg'],
  'application/pdf': ['.pdf'],
  'video/mp4': ['.mp4'],
  'video/webm': ['.webm']
};

// Taille max en bytes (10 MB)
const MAX_SIZE = 10 * 1024 * 1024;

/**
 * Liste les assets d'un projet
 * GET /api/projects/:projectId/assets
 */
exports.listAssets = async (req, res, next) => {
  try {
    const { projectId } = req.params;
    const { folder, type, limit = 100, offset = 0 } = req.query;

    const assets = await Asset.getByProject(projectId, {
      folder,
      type,
      limit: parseInt(limit),
      offset: parseInt(offset)
    });

    // Formater pour GrapesJS
    const baseUrl = `${req.protocol}://${req.get('host')}`;
    const formatted = assets.map(a => a.toGrapesFormat(baseUrl));

    res.json({
      success: true,
      data: {
        assets: formatted,
        total: assets.length
      }
    });
  } catch (error) {
    next(error);
  }
};

/**
 * Upload un ou plusieurs fichiers
 * POST /api/projects/:projectId/assets
 */
exports.uploadAsset = async (req, res, next) => {
  try {
    const { projectId } = req.params;
    const { folder = '', alt_text = '', tags = [] } = req.body;

    // Vérifier que des fichiers ont été envoyés
    if (!req.files || req.files.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Aucun fichier envoyé'
      });
    }

    const uploadedAssets = [];
    const errors = [];

    for (const file of req.files) {
      try {
        // Vérifier le type MIME
        if (!ALLOWED_TYPES[file.mimetype]) {
          errors.push({ file: file.originalname, error: 'Type de fichier non autorisé' });
          continue;
        }

        // Vérifier la taille
        if (file.size > MAX_SIZE) {
          errors.push({ file: file.originalname, error: 'Fichier trop volumineux (max 10MB)' });
          continue;
        }

        // Générer un nom unique
        const ext = path.extname(file.originalname).toLowerCase();
        const uniqueName = `${crypto.randomBytes(16).toString('hex')}${ext}`;
        const relativePath = `${projectId}/${folder ? folder + '/' : ''}${uniqueName}`;
        const fullPath = path.join(UPLOAD_DIR, relativePath);

        // Créer le dossier si nécessaire
        await fs.mkdir(path.dirname(fullPath), { recursive: true });

        // Écrire le fichier
        await fs.writeFile(fullPath, file.buffer);

        // Obtenir les dimensions si c'est une image
        let width = null;
        let height = null;
        if (file.mimetype.startsWith('image/')) {
          try {
            // Utiliser sharp si disponible, sinon laisser null
            const sharp = require('sharp');
            const metadata = await sharp(file.buffer).metadata();
            width = metadata.width;
            height = metadata.height;
          } catch (e) {
            // sharp non disponible, on continue sans dimensions
          }
        }

        // Créer l'entrée en base
        const asset = await Asset.create({
          project_id: projectId,
          original_name: file.originalname,
          filename: uniqueName,
          path: relativePath,
          mime_type: file.mimetype,
          size: file.size,
          width,
          height,
          alt_text: alt_text || file.originalname.replace(/\.[^/.]+$/, ''),
          tags: Array.isArray(tags) ? tags : [],
          folder
        });

        const baseUrl = `${req.protocol}://${req.get('host')}`;
        uploadedAssets.push(asset.toGrapesFormat(baseUrl));

      } catch (fileError) {
        logger.error('Erreur upload fichier:', fileError);
        errors.push({ file: file.originalname, error: fileError.message });
      }
    }

    res.status(201).json({
      success: true,
      message: `${uploadedAssets.length} fichier(s) uploadé(s)`,
      data: {
        assets: uploadedAssets,
        errors: errors.length > 0 ? errors : undefined
      }
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Met à jour un asset (alt_text, tags, folder)
 * PUT /api/projects/:projectId/assets/:assetId
 */
exports.updateAsset = async (req, res, next) => {
  try {
    const { projectId, assetId } = req.params;
    const { alt_text, tags, folder } = req.body;

    const asset = await Asset.findOne({
      where: { id: assetId, project_id: projectId }
    });

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: 'Asset non trouvé'
      });
    }

    // Mettre à jour les champs modifiables
    if (alt_text !== undefined) asset.alt_text = alt_text;
    if (tags !== undefined) asset.tags = tags;
    if (folder !== undefined) asset.folder = folder;

    await asset.save();

    const baseUrl = `${req.protocol}://${req.get('host')}`;

    res.json({
      success: true,
      message: 'Asset mis à jour',
      data: { asset: asset.toGrapesFormat(baseUrl) }
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Supprime un asset
 * DELETE /api/projects/:projectId/assets/:assetId
 */
exports.deleteAsset = async (req, res, next) => {
  try {
    const { projectId, assetId } = req.params;

    const asset = await Asset.findOne({
      where: { id: assetId, project_id: projectId }
    });

    if (!asset) {
      return res.status(404).json({
        success: false,
        message: 'Asset non trouvé'
      });
    }

    // Supprimer le fichier physique
    try {
      const fullPath = path.join(UPLOAD_DIR, asset.path);
      await fs.unlink(fullPath);
    } catch (fileError) {
      logger.warn('Impossible de supprimer le fichier:', fileError.message);
    }

    // Supprimer l'entrée en base
    await asset.destroy();

    res.json({
      success: true,
      message: 'Asset supprimé'
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Liste les dossiers d'un projet
 * GET /api/projects/:projectId/assets/folders
 */
exports.listFolders = async (req, res, next) => {
  try {
    const { projectId } = req.params;

    const folders = await Asset.findAll({
      where: { project_id: projectId },
      attributes: [[Asset.sequelize.fn('DISTINCT', Asset.sequelize.col('folder')), 'folder']],
      raw: true
    });

    res.json({
      success: true,
      data: {
        folders: folders.map(f => f.folder).filter(f => f !== '')
      }
    });

  } catch (error) {
    next(error);
  }
};
