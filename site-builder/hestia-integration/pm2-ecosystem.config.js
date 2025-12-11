/**
 * ===========================================
 * Configuration PM2 pour le Site Builder
 * ===========================================
 * 
 * PM2 est un process manager pour Node.js recommandé pour la production.
 * 
 * Installation:
 *   npm install -g pm2
 * 
 * Usage:
 *   pm2 start pm2-ecosystem.config.js
 *   pm2 status
 *   pm2 logs sitebuilder-api
 *   pm2 restart sitebuilder-api
 *   pm2 stop sitebuilder-api
 * 
 * Démarrage automatique au boot:
 *   pm2 startup
 *   pm2 save
 */

module.exports = {
  apps: [
    {
      // Nom de l'application
      name: 'sitebuilder-api',
      
      // Script à exécuter
      script: './backend/server.js',
      
      // Répertoire de travail
      cwd: '/opt/site-builder',
      
      // Nombre d'instances (0 = auto selon les CPU)
      instances: 1,
      
      // Mode cluster pour utiliser tous les CPU
      exec_mode: 'fork',
      
      // Redémarrer si la mémoire dépasse 500MB
      max_memory_restart: '500M',
      
      // Variables d'environnement
      env: {
        NODE_ENV: 'production',
        PORT: 3001
      },
      
      // Fichier d'environnement
      env_file: '/opt/site-builder/backend/.env',
      
      // Logs
      error_file: '/var/log/pm2/sitebuilder-error.log',
      out_file: '/var/log/pm2/sitebuilder-out.log',
      log_file: '/var/log/pm2/sitebuilder-combined.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      
      // Redémarrage automatique
      autorestart: true,
      watch: false, // Ne pas watch en production
      
      // Délai entre les redémarrages
      restart_delay: 4000,
      
      // Nombre max de redémarrages en 15 minutes
      max_restarts: 10,
      
      // Time before killing (graceful shutdown)
      kill_timeout: 5000
    }
  ]
};
