/**
 * ===========================================
 * Modèle Project
 * ===========================================
 * 
 * Représente un projet de site web créé avec le builder.
 * Lié à un utilisateur et à un domaine HestiaCP.
 * 
 * @field id - UUID unique
 * @field user_id - Référence vers l'utilisateur propriétaire
 * @field domain_name - Nom de domaine associé (ex: example.com)
 * @field project_name - Nom du projet (affiché dans l'interface)
 * @field publish_path - Chemin de publication personnalisé (optionnel)
 * @field settings - Paramètres du projet (couleurs, fonts, etc.)
 * @field is_published - Si le site est actuellement publié
 * @field last_published_at - Date de dernière publication
 */

const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const Project = sequelize.define('Project', {
    // Identifiant unique UUID
    id: {
      type: DataTypes.UUID,
      defaultValue: DataTypes.UUIDV4,
      primaryKey: true
    },

    // Référence vers l'utilisateur
    user_id: {
      type: DataTypes.UUID,
      allowNull: false,
      references: {
        model: 'users',
        key: 'id'
      }
    },

    // Nom de domaine associé au projet
    domain_name: {
      type: DataTypes.STRING(255),
      allowNull: false,
      validate: {
        notEmpty: true,
        // Validation basique du format domaine
        is: /^[a-zA-Z0-9][a-zA-Z0-9-_.]+\.[a-zA-Z]{2,}$/i
      },
      comment: 'Nom de domaine HestiaCP associé'
    },

    // Nom du projet (pour l'affichage)
    project_name: {
      type: DataTypes.STRING(255),
      allowNull: false,
      defaultValue: 'Mon Site',
      validate: {
        notEmpty: true,
        len: [1, 255]
      }
    },

    // Chemin de publication personnalisé (override le chemin par défaut)
    publish_path: {
      type: DataTypes.STRING(500),
      allowNull: true,
      comment: 'Chemin personnalisé de publication (optionnel)'
    },

    // Paramètres globaux du projet
    // Utilisé pour stocker les styles globaux, variables CSS, etc.
    settings: {
      type: DataTypes.JSONB,
      defaultValue: {
        // Couleurs par défaut
        colors: {
          primary: '#3b82f6',
          secondary: '#64748b',
          accent: '#f59e0b',
          background: '#ffffff',
          text: '#1f2937'
        },
        // Typographies par défaut
        fonts: {
          heading: 'Inter, sans-serif',
          body: 'Inter, sans-serif'
        },
        // Meta SEO
        seo: {
          title: '',
          description: '',
          keywords: ''
        }
      },
      comment: 'Paramètres globaux du projet (styles, SEO, etc.)'
    },

    // Status de publication
    is_published: {
      type: DataTypes.BOOLEAN,
      defaultValue: false,
      allowNull: false
    },

    // Date de dernière publication
    last_published_at: {
      type: DataTypes.DATE,
      allowNull: true
    },

    // Thumbnail du projet (base64 ou URL)
    thumbnail: {
      type: DataTypes.TEXT,
      allowNull: true,
      comment: 'Aperçu du projet'
    }
  }, {
    tableName: 'projects',
    timestamps: true,

    // Index pour optimiser les recherches
    indexes: [
      {
        fields: ['user_id']
      },
      {
        fields: ['domain_name']
      },
      {
        // Un utilisateur ne peut avoir qu'un projet par domaine
        unique: true,
        fields: ['user_id', 'domain_name']
      },
      {
        fields: ['is_published']
      }
    ]
  });

  // ===========================================
  // MÉTHODES D'INSTANCE
  // ===========================================

  /**
   * Met à jour le statut de publication
   */
  Project.prototype.markAsPublished = async function() {
    this.is_published = true;
    this.last_published_at = new Date();
    await this.save();
  };

  /**
   * Récupère le chemin de publication effectif
   * @param {string} basePath - Template du chemin de base
   * @param {string} username - Nom d'utilisateur HestiaCP
   * @returns {string} Chemin de publication complet
   */
  Project.prototype.getPublishPath = function(basePath, username) {
    // Si un chemin personnalisé est défini, l'utiliser
    if (this.publish_path) {
      return this.publish_path;
    }

    // Sinon, utiliser le template avec substitution
    return basePath
      .replace('{USERNAME}', username)
      .replace('{DOMAIN}', this.domain_name);
  };

  /**
   * Retourne le projet avec ses pages pour l'API
   */
  Project.prototype.toFullJSON = async function() {
    const pages = await this.getPages({
      order: [['order_index', 'ASC']]
    });

    return {
      id: this.id,
      domain_name: this.domain_name,
      project_name: this.project_name,
      settings: this.settings,
      is_published: this.is_published,
      last_published_at: this.last_published_at,
      thumbnail: this.thumbnail,
      created_at: this.created_at,
      updated_at: this.updated_at,
      pages: pages.map(page => page.toJSON())
    };
  };

  // ===========================================
  // MÉTHODES DE CLASSE
  // ===========================================

  /**
   * Trouve ou crée un projet pour un utilisateur et un domaine
   * 
   * @param {string} userId - ID de l'utilisateur
   * @param {string} domainName - Nom de domaine
   * @param {string} projectName - Nom du projet (optionnel)
   * @returns {Project} Instance du projet
   */
  Project.findOrCreateByDomain = async function(userId, domainName, projectName = null) {
    const [project, created] = await Project.findOrCreate({
      where: {
        user_id: userId,
        domain_name: domainName
      },
      defaults: {
        user_id: userId,
        domain_name: domainName,
        project_name: projectName || domainName
      }
    });

    // Si le projet vient d'être créé, ajouter une page d'accueil par défaut
    if (created) {
      const Page = sequelize.models.Page;
      await Page.create({
        project_id: project.id,
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        order_index: 0,
        grapesjs_data: Page.getDefaultPageData()
      });
    }

    return { project, created };
  };

  return Project;
};
