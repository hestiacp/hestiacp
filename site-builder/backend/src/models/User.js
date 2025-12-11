/**
 * ===========================================
 * Modèle User
 * ===========================================
 * 
 * Représente un utilisateur du Site Builder.
 * Peut être créé via SSO depuis HestiaCP ou manuellement.
 * 
 * @field id - UUID unique
 * @field hestia_username - Nom d'utilisateur HestiaCP (unique)
 * @field email - Email de l'utilisateur (optionnel)
 * @field role - Rôle de l'utilisateur (admin, user)
 * @field last_login - Date de dernière connexion
 * @field settings - Paramètres utilisateur en JSON
 */

const { DataTypes } = require('sequelize');

module.exports = (sequelize) => {
  const User = sequelize.define('User', {
    // Identifiant unique UUID
    id: {
      type: DataTypes.UUID,
      defaultValue: DataTypes.UUIDV4,
      primaryKey: true
    },

    // Nom d'utilisateur HestiaCP - clé d'identification SSO
    hestia_username: {
      type: DataTypes.STRING(100),
      allowNull: false,
      unique: true,
      validate: {
        notEmpty: true,
        // Validation du format username HestiaCP
        is: /^[a-zA-Z0-9_-]+$/i
      },
      comment: 'Nom utilisateur HestiaCP pour le SSO'
    },

    // Email (optionnel, peut être récupéré de HestiaCP)
    email: {
      type: DataTypes.STRING(255),
      allowNull: true,
      validate: {
        isEmail: true
      }
    },

    // Rôle de l'utilisateur
    role: {
      type: DataTypes.ENUM('admin', 'user'),
      defaultValue: 'user',
      allowNull: false
    },

    // Statut du compte
    is_active: {
      type: DataTypes.BOOLEAN,
      defaultValue: true,
      allowNull: false
    },

    // Date de dernière connexion
    last_login: {
      type: DataTypes.DATE,
      allowNull: true
    },

    // Paramètres utilisateur personnalisés (JSON)
    // Extensible pour : langue, thème, préférences GrapesJS, etc.
    settings: {
      type: DataTypes.JSONB,
      defaultValue: {},
      comment: 'Paramètres utilisateur personnalisés'
    }
  }, {
    tableName: 'users',
    timestamps: true,
    
    // Index pour optimiser les recherches
    indexes: [
      {
        unique: true,
        fields: ['hestia_username']
      },
      {
        fields: ['email']
      },
      {
        fields: ['is_active']
      }
    ]
  });

  // ===========================================
  // MÉTHODES D'INSTANCE
  // ===========================================

  /**
   * Met à jour la date de dernière connexion
   */
  User.prototype.updateLastLogin = async function() {
    this.last_login = new Date();
    await this.save();
  };

  /**
   * Retourne une version sécurisée de l'utilisateur (sans données sensibles)
   */
  User.prototype.toSafeJSON = function() {
    return {
      id: this.id,
      hestia_username: this.hestia_username,
      email: this.email,
      role: this.role,
      is_active: this.is_active,
      last_login: this.last_login,
      settings: this.settings,
      created_at: this.created_at
    };
  };

  // ===========================================
  // MÉTHODES DE CLASSE
  // ===========================================

  /**
   * Trouve ou crée un utilisateur par son username HestiaCP
   * Utilisé lors de l'authentification SSO
   * 
   * @param {string} hestiaUsername - Nom d'utilisateur HestiaCP
   * @param {object} additionalData - Données supplémentaires (email, etc.)
   * @returns {User} Instance de l'utilisateur
   */
  User.findOrCreateByHestiaUsername = async function(hestiaUsername, additionalData = {}) {
    const [user, created] = await User.findOrCreate({
      where: { hestia_username: hestiaUsername },
      defaults: {
        hestia_username: hestiaUsername,
        email: additionalData.email || null,
        settings: additionalData.settings || {}
      }
    });

    // Mettre à jour la dernière connexion
    await user.updateLastLogin();

    return { user, created };
  };

  return User;
};
