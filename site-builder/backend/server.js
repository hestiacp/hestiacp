/**
 * ===========================================
 * HestiaCP Site Builder - Point d'entrée serveur
 * ===========================================
 * 
 * Ce fichier initialise et démarre le serveur Express.
 * Configuration de la base de données et démarrage de l'API.
 * 
 * @author HestiaCP Site Builder
 * @version 1.0.0
 */

require('dotenv').config();

const app = require('./src/app');
const { sequelize } = require('./src/models');
const logger = require('./src/config/logger');

const PORT = process.env.PORT || 3001;

/**
 * Fonction principale de démarrage du serveur
 * - Teste la connexion à la base de données
 * - Synchronise les modèles (en dev uniquement)
 * - Démarre le serveur HTTP
 */
async function startServer() {
  try {
    // Test de la connexion à la base de données
    await sequelize.authenticate();
    logger.info('✅ Connexion à la base de données établie avec succès');

    // En développement, synchroniser automatiquement les modèles
    // En production, utiliser les migrations Sequelize
    if (process.env.NODE_ENV === 'development') {
      await sequelize.sync({ alter: true });
      logger.info('✅ Modèles synchronisés avec la base de données');
    }

    // Démarrage du serveur
    app.listen(PORT, () => {
      logger.info(`🚀 Serveur démarré sur le port ${PORT}`);
      logger.info(`📍 Environment: ${process.env.NODE_ENV || 'development'}`);
      logger.info(`🔗 API disponible sur: http://localhost:${PORT}/api`);
    });

  } catch (error) {
    logger.error('❌ Impossible de démarrer le serveur:', error);
    process.exit(1);
  }
}

// Gestion des erreurs non capturées
process.on('uncaughtException', (error) => {
  logger.error('Uncaught Exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
});

// Arrêt gracieux
process.on('SIGTERM', async () => {
  logger.info('SIGTERM reçu. Fermeture gracieuse...');
  await sequelize.close();
  process.exit(0);
});

// Démarrage
startServer();
