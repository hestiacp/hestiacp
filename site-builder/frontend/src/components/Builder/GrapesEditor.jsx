/**
 * ===========================================
 * Composant GrapesJS Editor
 * ===========================================
 * 
 * Initialise et configure l'éditeur GrapesJS.
 * Gère le chargement des données et la configuration des plugins.
 */

import { useEffect, useRef } from 'react';
import grapesjs from 'grapesjs';

// Plugins GrapesJS
import gjsBlocksBasic from 'grapesjs-blocks-basic';
import gjsPluginForms from 'grapesjs-plugin-forms';
import gjsPresetWebpage from 'grapesjs-preset-webpage';
import gjsStyleBg from 'grapesjs-style-bg';

// Blocs personnalisés
import { registerCustomBlocks } from '../blocks';

function GrapesEditor({ currentPage, projectSettings, onEditorReady }) {
  const editorRef = useRef(null);
  const editorInstance = useRef(null);

  /**
   * Initialise GrapesJS au montage du composant
   */
  useEffect(() => {
    if (editorInstance.current) {
      // Éditeur déjà initialisé
      return;
    }

    // Configuration de l'éditeur
    const editor = grapesjs.init({
      container: '#gjs',
      
      // Dimensions du canvas
      height: '100%',
      width: 'auto',
      
      // Désactiver le stockage local (on utilise notre API)
      storageManager: false,
      
      // Configuration du canvas
      canvas: {
        styles: [
          'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
        ]
      },
      
      // Configuration des devices
      deviceManager: {
        devices: [
          {
            name: 'Desktop',
            width: ''
          },
          {
            name: 'Tablet',
            width: '768px',
            widthMedia: '992px'
          },
          {
            name: 'Mobile portrait',
            width: '320px',
            widthMedia: '480px'
          }
        ]
      },

      // Configuration des panneaux
      panels: {
        defaults: []
      },

      // Configuration du Style Manager
      styleManager: {
        appendTo: '#styles-container',
        sectors: [
          {
            name: 'Général',
            open: true,
            buildProps: ['float', 'display', 'position', 'top', 'right', 'left', 'bottom']
          },
          {
            name: 'Dimensions',
            open: false,
            buildProps: ['width', 'height', 'max-width', 'min-height', 'margin', 'padding']
          },
          {
            name: 'Typographie',
            open: false,
            buildProps: [
              'font-family', 'font-size', 'font-weight', 'letter-spacing',
              'color', 'line-height', 'text-align', 'text-decoration', 'text-shadow'
            ]
          },
          {
            name: 'Arrière-plan',
            open: false,
            buildProps: ['background-color', 'background']
          },
          {
            name: 'Bordures',
            open: false,
            buildProps: ['border-radius', 'border', 'box-shadow']
          },
          {
            name: 'Extra',
            open: false,
            buildProps: ['opacity', 'transition', 'transform']
          }
        ]
      },

      // Configuration du Block Manager
      blockManager: {
        appendTo: '#blocks-container'
      },

      // Configuration du Layer Manager
      layerManager: {
        appendTo: '#layers-container'
      },

      // Configuration du Trait Manager
      traitManager: {
        appendTo: '#traits-container'
      },

      // Configuration du Selector Manager
      selectorManager: {
        appendTo: '#selectors-container'
      },

      // Plugins
      plugins: [
        gjsBlocksBasic,
        gjsPluginForms,
        gjsPresetWebpage,
        gjsStyleBg
      ],

      // Options des plugins
      pluginsOpts: {
        [gjsBlocksBasic]: {
          flexGrid: true,
          blocks: ['column1', 'column2', 'column3', 'column3-7', 'text', 'link', 'image', 'video', 'map']
        },
        [gjsPluginForms]: {
          blocks: ['form', 'input', 'textarea', 'select', 'button', 'label', 'checkbox', 'radio']
        },
        [gjsPresetWebpage]: {
          modalImportTitle: 'Importer du code',
          modalImportLabel: '<div style="margin-bottom: 10px;">Collez votre HTML/CSS ici</div>',
          modalImportContent: '',
          importViewerRecovery: true,
          countdownRecovery: 5,
          showStylesOnChange: true,
          customStyleManager: []
        }
      }
    });

    // Stocker l'instance globalement pour l'accès depuis le header
    window.grapesEditor = editor;
    editorInstance.current = editor;

    // Enregistrer les blocs personnalisés
    registerCustomBlocks(editor);

    // Ajouter des styles CSS par défaut dans le canvas
    const defaultStyles = `
      * {
        box-sizing: border-box;
      }
      body {
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
      }
      section {
        padding: 60px 20px;
      }
      h1, h2, h3, h4, h5, h6 {
        margin: 0 0 16px 0;
        font-weight: 600;
      }
      p {
        margin: 0 0 16px 0;
        line-height: 1.6;
      }
      img {
        max-width: 100%;
        height: auto;
      }
      a {
        color: #3b82f6;
        text-decoration: none;
      }
      a:hover {
        text-decoration: underline;
      }
    `;

    editor.on('load', () => {
      // Ajouter les styles par défaut
      editor.addStyle(defaultStyles);

      // Configurer les raccourcis clavier
      document.addEventListener('keydown', (e) => {
        // Ctrl+S pour sauvegarder
        if (e.ctrlKey && e.key === 's') {
          e.preventDefault();
          editor.trigger('storage:save');
        }
      });
    });

    // Callback quand l'éditeur est prêt
    if (onEditorReady) {
      onEditorReady(editor);
    }

    // Cleanup au démontage
    return () => {
      if (editorInstance.current) {
        editorInstance.current.destroy();
        editorInstance.current = null;
        window.grapesEditor = null;
      }
    };
  }, [onEditorReady]);

  /**
   * Met à jour le contenu quand la page change
   */
  useEffect(() => {
    if (!editorInstance.current || !currentPage) return;

    const editor = editorInstance.current;

    // Charger les composants de la page
    const components = currentPage.grapesjs_data?.components || [];
    const styles = currentPage.grapesjs_data?.styles || [];

    // Mettre à jour l'éditeur
    editor.setComponents(components);
    
    // Ajouter les styles (en préservant les styles par défaut)
    if (styles.length > 0) {
      editor.addStyle(styles);
    }

    // Charger les assets si disponibles
    const assets = currentPage.grapesjs_data?.assets || [];
    if (assets.length > 0) {
      editor.AssetManager.add(assets);
    }

  }, [currentPage]);

  /**
   * Met à jour les variables CSS selon les settings du projet
   */
  useEffect(() => {
    if (!editorInstance.current || !projectSettings) return;

    const editor = editorInstance.current;
    const { colors = {}, fonts = {} } = projectSettings;

    // Créer les variables CSS
    const cssVars = `
      :root {
        --color-primary: ${colors.primary || '#3b82f6'};
        --color-secondary: ${colors.secondary || '#64748b'};
        --color-accent: ${colors.accent || '#f59e0b'};
        --color-background: ${colors.background || '#ffffff'};
        --color-text: ${colors.text || '#1f2937'};
        --font-heading: ${fonts.heading || 'Inter, sans-serif'};
        --font-body: ${fonts.body || 'Inter, sans-serif'};
      }
    `;

    // Injecter dans le canvas
    const frame = editor.Canvas.getFrameEl();
    if (frame) {
      const doc = frame.contentDocument;
      let styleEl = doc.getElementById('project-css-vars');
      
      if (!styleEl) {
        styleEl = doc.createElement('style');
        styleEl.id = 'project-css-vars';
        doc.head.appendChild(styleEl);
      }
      
      styleEl.textContent = cssVars;
    }
  }, [projectSettings]);

  return (
    <div 
      id="gjs" 
      ref={editorRef}
      className="h-full"
    />
  );
}

export default GrapesEditor;
