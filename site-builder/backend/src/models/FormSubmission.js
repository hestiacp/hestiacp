/**
 * ===========================================
 * Modèle FormSubmission
 * ===========================================
 * 
 * Stocke les soumissions de formulaires créés dans le builder.
 */

const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const FormSubmission = sequelize.define('FormSubmission', {
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

    // ID optionnel de la page où se trouve le formulaire
    page_id: {
      type: DataTypes.UUID,
      allowNull: true,
      references: {
        model: 'pages',
        key: 'id'
      }
    },

    // Identifiant unique du formulaire (attribut data-form-id dans le HTML)
    form_id: {
      type: DataTypes.STRING(100),
      allowNull: true
    },

    // Nom du formulaire (pour identification)
    form_name: {
      type: DataTypes.STRING(255),
      allowNull: true,
      defaultValue: 'Contact Form'
    },

    // Données soumises
    data: {
      type: DataTypes.JSONB,
      allowNull: false,
      defaultValue: {}
    },

    // Informations sur le visiteur
    visitor_info: {
      type: DataTypes.JSONB,
      defaultValue: {},
      comment: 'IP, User-Agent, Referer, etc.'
    },

    // Statut de la soumission
    status: {
      type: DataTypes.ENUM('new', 'read', 'replied', 'archived', 'spam'),
      defaultValue: 'new',
      allowNull: false
    },

    // Email de notification envoyé ?
    notification_sent: {
      type: DataTypes.BOOLEAN,
      defaultValue: false
    },

    // Notes internes
    notes: {
      type: DataTypes.TEXT,
      allowNull: true
    }
  }, {
    tableName: 'form_submissions',
    timestamps: true,

    indexes: [
      { fields: ['project_id'] },
      { fields: ['page_id'] },
      { fields: ['form_id'] },
      { fields: ['status'] },
      { fields: ['created_at'] }
    ]
  });

  /**
   * Récupère les soumissions d'un projet
   */
  FormSubmission.getByProject = async function(projectId, options = {}) {
    const where = { project_id: projectId };
    
    if (options.formId) {
      where.form_id = options.formId;
    }
    
    if (options.status) {
      where.status = options.status;
    }

    return await FormSubmission.findAndCountAll({
      where,
      order: [['created_at', 'DESC']],
      limit: options.limit || 50,
      offset: options.offset || 0
    });
  };

  /**
   * Compte les nouvelles soumissions non lues
   */
  FormSubmission.countNew = async function(projectId) {
    return await FormSubmission.count({
      where: { 
        project_id: projectId, 
        status: 'new' 
      }
    });
  };

  return FormSubmission;
};
