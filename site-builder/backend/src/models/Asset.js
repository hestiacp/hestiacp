/**
 * ===========================================
 * Modèle Asset (Media Manager)
 * ===========================================
 * 
 * Gère les fichiers uploadés (images, vidéos, documents).
 * Chaque asset appartient à un projet.
 */

const { DataTypes } = require('sequelize');
const path = require('path');

module.exports = (sequelize) => {
  const Asset = sequelize.define('Asset', {
    id: {
      type: DataTypes.UUID,
      defaultValue: DataTypes.UUIDV4,
      primaryKey: true
    },

    project_id: {
      type: DataTypes.UUID,
      allowNull: false,
      references: {
        model: 'projects',
        key: 'id'
      }
    },

    // Nom original du fichier
    original_name: {
      type: DataTypes.STRING(255),
      allowNull: false
    },

    // Nom du fichier stocké (peut être différent pour éviter les conflits)
    filename: {
      type: DataTypes.STRING(255),
      allowNull: false
    },

    // Chemin relatif depuis le dossier assets
    path: {
      type: DataTypes.STRING(500),
      allowNull: false
    },

    // Type MIME
    mime_type: {
      type: DataTypes.STRING(100),
      allowNull: false
    },

    // Taille en bytes
    size: {
      type: DataTypes.INTEGER,
      allowNull: false,
      defaultValue: 0
    },

    // Dimensions pour les images
    width: {
      type: DataTypes.INTEGER,
      allowNull: true
    },

    height: {
      type: DataTypes.INTEGER,
      allowNull: true
    },

    // Alt text pour les images
    alt_text: {
      type: DataTypes.STRING(255),
      allowNull: true,
      defaultValue: ''
    },

    // Tags/catégories pour organisation
    tags: {
      type: DataTypes.ARRAY(DataTypes.STRING),
      defaultValue: [],
      allowNull: false
    },

    // Dossier virtuel pour organisation
    folder: {
      type: DataTypes.STRING(100),
      defaultValue: '',
      allowNull: false
    },

    // Métadonnées supplémentaires
    metadata: {
      type: DataTypes.JSONB,
      defaultValue: {},
      allowNull: false
    }
  }, {
    tableName: 'assets',
    timestamps: true,

    indexes: [
      { fields: ['project_id'] },
      { fields: ['mime_type'] },
      { fields: ['folder'] },
      { fields: ['tags'], using: 'gin' }
    ]
  });

  /**
   * Retourne l'URL publique de l'asset
   */
  Asset.prototype.getPublicUrl = function(baseUrl = '') {
    return `${baseUrl}/uploads/${this.path}`;
  };

  /**
   * Vérifie si c'est une image
   */
  Asset.prototype.isImage = function() {
    return this.mime_type.startsWith('image/');
  };

  /**
   * Retourne les infos formatées pour GrapesJS AssetManager
   */
  Asset.prototype.toGrapesFormat = function(baseUrl = '') {
    return {
      src: this.getPublicUrl(baseUrl),
      name: this.original_name,
      type: this.isImage() ? 'image' : 'file',
      width: this.width,
      height: this.height,
      id: this.id
    };
  };

  /**
   * Récupère tous les assets d'un projet
   */
  Asset.getByProject = async function(projectId, options = {}) {
    const where = { project_id: projectId };
    
    if (options.folder) {
      where.folder = options.folder;
    }
    
    if (options.type === 'image') {
      where.mime_type = { [sequelize.Sequelize.Op.like]: 'image/%' };
    }

    return await Asset.findAll({
      where,
      order: [['created_at', 'DESC']],
      limit: options.limit || 100,
      offset: options.offset || 0
    });
  };

  return Asset;
};
