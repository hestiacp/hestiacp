/**
 * ===========================================
 * Configuration Sequelize pour la base de données
 * ===========================================
 * 
 * Configuration de connexion PostgreSQL pour Sequelize.
 * Supporte les environnements dev, test et production.
 */

const config = require('./index');

module.exports = {
  development: {
    username: config.database.user,
    password: config.database.password,
    database: config.database.name,
    host: config.database.host,
    port: config.database.port,
    dialect: 'postgres',
    logging: console.log,
    define: {
      timestamps: true,
      underscored: true, // Utilise snake_case pour les colonnes
      freezeTableName: true
    }
  },
  
  test: {
    username: config.database.user,
    password: config.database.password,
    database: `${config.database.name}_test`,
    host: config.database.host,
    port: config.database.port,
    dialect: 'postgres',
    logging: false
  },
  
  production: {
    username: config.database.user,
    password: config.database.password,
    database: config.database.name,
    host: config.database.host,
    port: config.database.port,
    dialect: 'postgres',
    logging: false,
    pool: {
      max: 10,
      min: 2,
      acquire: 30000,
      idle: 10000
    },
    dialectOptions: {
      ssl: {
        require: true,
        rejectUnauthorized: false
      }
    }
  }
};
