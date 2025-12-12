/**
 * ===========================================
 * Index des modèles Sequelize
 * ===========================================
 * 
 * Initialise Sequelize et charge tous les modèles.
 * Configure les associations entre les modèles.
 */

const { Sequelize } = require('sequelize');
const config = require('../config');
const logger = require('../config/logger');

// Création de l'instance Sequelize
const sequelize = new Sequelize(
  config.database.name,
  config.database.user,
  config.database.password,
  {
    host: config.database.host,
    port: config.database.port,
    dialect: config.database.dialect,
    logging: config.database.logging ? (msg) => logger.debug(msg) : false,
    pool: config.database.pool,
    define: {
      timestamps: true,
      underscored: true,
      freezeTableName: true
    }
  }
);

// Import des modèles
const User = require('./User')(sequelize);
const Project = require('./Project')(sequelize);
const Page = require('./Page')(sequelize);
const Asset = require('./Asset')(sequelize);
const FormSubmission = require('./FormSubmission')(sequelize);

// ===========================================
// ASSOCIATIONS ENTRE LES MODÈLES
// ===========================================

// Un utilisateur a plusieurs projets
User.hasMany(Project, {
  foreignKey: 'user_id',
  as: 'projects',
  onDelete: 'CASCADE'
});

Project.belongsTo(User, {
  foreignKey: 'user_id',
  as: 'user'
});

// Un projet a plusieurs pages
Project.hasMany(Page, {
  foreignKey: 'project_id',
  as: 'pages',
  onDelete: 'CASCADE'
});

Page.belongsTo(Project, {
  foreignKey: 'project_id',
  as: 'project'
});

// Un projet a plusieurs assets
Project.hasMany(Asset, {
  foreignKey: 'project_id',
  as: 'assets',
  onDelete: 'CASCADE'
});

Asset.belongsTo(Project, {
  foreignKey: 'project_id',
  as: 'project'
});

// Un projet a plusieurs soumissions de formulaire
Project.hasMany(FormSubmission, {
  foreignKey: 'project_id',
  as: 'formSubmissions',
  onDelete: 'CASCADE'
});

FormSubmission.belongsTo(Project, {
  foreignKey: 'project_id',
  as: 'project'
});

// Une page peut avoir plusieurs soumissions
Page.hasMany(FormSubmission, {
  foreignKey: 'page_id',
  as: 'formSubmissions',
  onDelete: 'SET NULL'
});

FormSubmission.belongsTo(Page, {
  foreignKey: 'page_id',
  as: 'page'
});

// Export de tous les modèles et de Sequelize
module.exports = {
  sequelize,
  Sequelize,
  User,
  Project,
  Page,
  Asset,
  FormSubmission
};
