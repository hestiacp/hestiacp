/**
 * ===========================================
 * Modèle Page
 * ===========================================
 * 
 * Représente une page d'un projet de site web.
 * Stocke les données GrapesJS (composants, styles, assets).
 * 
 * @field id - UUID unique
 * @field project_id - Référence vers le projet parent
 * @field name - Nom de la page (affiché dans l'interface)
 * @field slug - Slug URL (ex: "about" pour about.html)
 * @field grapesjs_data - Données complètes GrapesJS (JSONB)
 * @field is_homepage - Si c'est la page d'accueil
 * @field order_index - Ordre d'affichage dans la liste
 * @field seo - Métadonnées SEO spécifiques à la page
 */

const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const Page = sequelize.define('Page', {
    // Identifiant unique UUID
    id: {
      type: DataTypes.UUID,
      defaultValue: DataTypes.UUIDV4,
      primaryKey: true
    },

    // Référence vers le projet
    project_id: {
      type: DataTypes.UUID,
      allowNull: false,
      references: {
        model: 'projects',
        key: 'id'
      }
    },

    // Nom de la page (affiché dans l'UI)
    name: {
      type: DataTypes.STRING(100),
      allowNull: false,
      defaultValue: 'Nouvelle page',
      validate: {
        notEmpty: true,
        len: [1, 100]
      }
    },

    // Slug URL de la page
    slug: {
      type: DataTypes.STRING(100),
      allowNull: false,
      defaultValue: 'page',
      validate: {
        notEmpty: true,
        // Format slug valide
        is: /^[a-z0-9-]+$/i
      },
      comment: 'Slug URL (ex: about -> about.html)'
    },

    // Données GrapesJS complètes
    // Structure: { components: [], styles: [], assets: [] }
    grapesjs_data: {
      type: DataTypes.JSONB,
      allowNull: false,
      defaultValue: {
        components: [],
        styles: [],
        assets: []
      },
      comment: 'Données GrapesJS (composants, styles, assets)'
    },

    // Page d'accueil (une seule par projet)
    is_homepage: {
      type: DataTypes.BOOLEAN,
      defaultValue: false,
      allowNull: false
    },

    // Ordre d'affichage
    order_index: {
      type: DataTypes.INTEGER,
      defaultValue: 0,
      allowNull: false
    },

    // Métadonnées SEO spécifiques à la page
    seo: {
      type: DataTypes.JSONB,
      defaultValue: {
        title: '',
        description: '',
        keywords: '',
        og_image: ''
      },
      comment: 'Métadonnées SEO de la page'
    }
  }, {
    tableName: 'pages',
    timestamps: true,

    // Index pour optimiser les recherches
    indexes: [
      {
        fields: ['project_id']
      },
      {
        // Un slug unique par projet
        unique: true,
        fields: ['project_id', 'slug']
      },
      {
        fields: ['is_homepage']
      },
      {
        fields: ['order_index']
      }
    ],

    // Hooks
    hooks: {
      // Avant création, s'assurer qu'il n'y a qu'une seule homepage
      beforeCreate: async (page) => {
        if (page.is_homepage) {
          await Page.update(
            { is_homepage: false },
            { where: { project_id: page.project_id, is_homepage: true } }
          );
        }
      },
      // Avant mise à jour, gérer le changement de homepage
      beforeUpdate: async (page) => {
        if (page.changed('is_homepage') && page.is_homepage) {
          await Page.update(
            { is_homepage: false },
            { 
              where: { 
                project_id: page.project_id, 
                is_homepage: true,
                id: { [sequelize.Sequelize.Op.ne]: page.id }
              } 
            }
          );
        }
      }
    }
  });

  // ===========================================
  // MÉTHODES D'INSTANCE
  // ===========================================

  /**
   * Génère le nom de fichier HTML pour cette page
   * @returns {string} Nom du fichier (ex: "about.html" ou "index.html")
   */
  Page.prototype.getFileName = function() {
    if (this.is_homepage || this.slug === 'index') {
      return 'index.html';
    }
    return `${this.slug}.html`;
  };

  /**
   * Met à jour les données GrapesJS
   * @param {object} data - Nouvelles données GrapesJS
   */
  Page.prototype.updateGrapesData = async function(data) {
    this.grapesjs_data = {
      components: data.components || this.grapesjs_data.components,
      styles: data.styles || this.grapesjs_data.styles,
      assets: data.assets || this.grapesjs_data.assets
    };
    await this.save();
  };

  // ===========================================
  // MÉTHODES DE CLASSE
  // ===========================================

  /**
   * Retourne les données par défaut pour une nouvelle page
   * Contient une structure de base avec header et section vide
   */
  Page.getDefaultPageData = function() {
    return {
      // Composants par défaut - une section vide
      components: [
        {
          type: 'section',
          classes: ['section', 'min-h-screen', 'p-8'],
          components: [
            {
              type: 'text',
              content: '<h1>Bienvenue sur votre nouvelle page</h1><p>Commencez à créer votre site en ajoutant des blocs depuis le panneau de gauche.</p>',
              classes: ['text-center']
            }
          ]
        }
      ],
      // Styles par défaut
      styles: [
        {
          selectors: ['section'],
          style: {
            'padding': '40px 20px',
            'min-height': '200px'
          }
        }
      ],
      // Assets (images, etc.)
      assets: []
    };
  };

  /**
   * Récupère toutes les pages d'un projet ordonnées
   * @param {string} projectId - ID du projet
   * @returns {Page[]} Liste des pages ordonnées
   */
  Page.getByProject = async function(projectId) {
    return await Page.findAll({
      where: { project_id: projectId },
      order: [
        ['is_homepage', 'DESC'], // Homepage en premier
        ['order_index', 'ASC'],
        ['created_at', 'ASC']
      ]
    });
  };

  return Page;
};
