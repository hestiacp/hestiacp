import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/**
 * Configuration Vite pour le Site Builder
 * https://vitejs.dev/config/
 */
export default defineConfig({
  plugins: [react()],
  
  server: {
    port: 5173,
    // Proxy vers le backend en développement
    proxy: {
      '/api': {
        target: 'http://localhost:3001',
        changeOrigin: true,
        secure: false
      }
    }
  },
  
  build: {
    outDir: 'dist',
    sourcemap: true,
    // Optimisation pour les librairies volumineuses comme GrapesJS
    rollupOptions: {
      output: {
        manualChunks: {
          'grapesjs': ['grapesjs'],
          'grapesjs-plugins': [
            'grapesjs-blocks-basic',
            'grapesjs-plugin-forms',
            'grapesjs-preset-webpage'
          ],
          'react-vendor': ['react', 'react-dom', 'react-router-dom']
        }
      }
    }
  },
  
  // Optimisation des dépendances
  optimizeDeps: {
    include: [
      'grapesjs',
      'grapesjs-blocks-basic',
      'grapesjs-plugin-forms',
      'grapesjs-preset-webpage'
    ]
  }
});
