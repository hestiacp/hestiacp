/**
 * ===========================================
 * Composant GrapesJS Editor - Version améliorée
 * ===========================================
 * 
 * Intègre GrapesJS avec :
 * - Asset Manager connecté au backend
 * - Blocs personnalisés
 * - Configuration responsive
 * - Design system
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

// API
import { assetApi } from '../../services/api';

function GrapesEditor({ currentPage, projectSettings, projectId, onEditorReady }) {
  const editorRef = useRef(null);
  const editorInstance = useRef(null);

  useEffect(() => {
    if (editorInstance.current) {
      return;
    }

    // Configuration de l'éditeur
    const editor = grapesjs.init({
      container: '#gjs',
      height: '100%',
      width: 'auto',
      storageManager: false,
      
      // Canvas
      canvas: {
        styles: [
          'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
        ]
      },
      
      // Devices pour responsive
      deviceManager: {
        devices: [
          { name: 'Desktop', width: '' },
          { name: 'Tablet', width: '768px', widthMedia: '992px' },
          { name: 'Mobile landscape', width: '568px', widthMedia: '768px' },
          { name: 'Mobile portrait', width: '320px', widthMedia: '480px' }
        ]
      },

      // Panneaux
      panels: { defaults: [] },

      // Style Manager
      styleManager: {
        appendTo: '#styles-container',
        sectors: [
          {
            name: 'Général',
            open: true,
            buildProps: ['float', 'display', 'position', 'top', 'right', 'left', 'bottom', 'z-index']
          },
          {
            name: 'Flex',
            open: false,
            buildProps: ['flex-direction', 'flex-wrap', 'justify-content', 'align-items', 'align-content', 'gap', 'order', 'flex-basis', 'flex-grow', 'flex-shrink', 'align-self']
          },
          {
            name: 'Dimensions',
            open: false,
            buildProps: ['width', 'height', 'max-width', 'min-width', 'max-height', 'min-height', 'margin', 'padding']
          },
          {
            name: 'Typographie',
            open: false,
            buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height', 'text-align', 'text-decoration', 'text-shadow', 'text-transform']
          },
          {
            name: 'Arrière-plan',
            open: false,
            buildProps: ['background-color', 'background-image', 'background-repeat', 'background-position', 'background-attachment', 'background-size']
          },
          {
            name: 'Bordures',
            open: false,
            buildProps: ['border-radius', 'border', 'border-width', 'border-style', 'border-color', 'box-shadow']
          },
          {
            name: 'Extra',
            open: false,
            buildProps: ['opacity', 'transition', 'transform', 'cursor', 'overflow']
          }
        ]
      },

      // Block Manager
      blockManager: {
        appendTo: '#blocks-container'
      },

      // Layer Manager
      layerManager: {
        appendTo: '#layers-container'
      },

      // Trait Manager
      traitManager: {
        appendTo: '#traits-container'
      },

      // Selector Manager
      selectorManager: {
        appendTo: '#selectors-container',
        componentFirst: true
      },

      // Asset Manager avec upload
      assetManager: {
        appendTo: '#assets-container',
        upload: projectId ? `/builder/api/projects/${projectId}/assets` : false,
        uploadName: 'files',
        multiUpload: true,
        autoAdd: true,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('sitebuilder_token')}`
        },
        credentials: 'include',
        // Charger les assets existants
        assets: [],
        // Upload callback
        uploadFile: async (e) => {
          const files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
          const formData = new FormData();
          
          for (let i = 0; i < files.length; i++) {
            formData.append('files', files[i]);
          }
          
          try {
            const response = await assetApi.upload(projectId, formData);
            if (response.data.success) {
              const assets = response.data.data.assets;
              assets.forEach(asset => {
                editor.AssetManager.add(asset);
              });
            }
          } catch (error) {
            console.error('Erreur upload:', error);
          }
        }
      },

      // Plugins
      plugins: [
        gjsBlocksBasic,
        gjsPluginForms,
        gjsPresetWebpage,
        gjsStyleBg
      ],

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
          importViewerRecovery: true,
          countdownRecovery: 5,
          showStylesOnChange: true
        }
      }
    });

    // Stocker l'instance
    window.grapesEditor = editor;
    editorInstance.current = editor;

    // Enregistrer les blocs personnalisés
    registerCustomBlocks(editor);

    // Styles CSS par défaut
    const defaultStyles = `
      * { box-sizing: border-box; }
      body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
      .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
      section { padding: 60px 20px; }
      h1, h2, h3, h4, h5, h6 { margin: 0 0 16px 0; font-weight: 600; }
      p { margin: 0 0 16px 0; line-height: 1.6; }
      img { max-width: 100%; height: auto; }
      a { color: #3b82f6; text-decoration: none; }
      a:hover { text-decoration: underline; }
    `;

    editor.on('load', async () => {
      editor.addStyle(defaultStyles);

      // Charger les assets existants du projet
      if (projectId) {
        try {
          const response = await assetApi.list(projectId);
          if (response.data.success && response.data.data.assets) {
            editor.AssetManager.add(response.data.data.assets);
          }
        } catch (error) {
          console.error('Erreur chargement assets:', error);
        }
      }

      // Raccourcis clavier
      document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 's') {
          e.preventDefault();
          editor.trigger('storage:save');
        }
      });
    });

    // Ajouter des commandes personnalisées
    editor.Commands.add('show-assets', {
      run(editor) {
        editor.AssetManager.open();
      }
    });

    // Callback
    if (onEditorReady) {
      onEditorReady(editor);
    }

    return () => {
      if (editorInstance.current) {
        editorInstance.current.destroy();
        editorInstance.current = null;
        window.grapesEditor = null;
      }
    };
  }, [onEditorReady, projectId]);

  // Mise à jour du contenu quand la page change
  useEffect(() => {
    if (!editorInstance.current || !currentPage) return;

    const editor = editorInstance.current;
    const components = currentPage.grapesjs_data?.components || [];
    const styles = currentPage.grapesjs_data?.styles || [];

    editor.setComponents(components);
    if (styles.length > 0) {
      editor.addStyle(styles);
    }

    const assets = currentPage.grapesjs_data?.assets || [];
    if (assets.length > 0) {
      editor.AssetManager.add(assets);
    }
  }, [currentPage]);

  // Injection des variables CSS du projet
  useEffect(() => {
    if (!editorInstance.current || !projectSettings) return;

    const editor = editorInstance.current;
    const { colors = {}, fonts = {}, spacing = {} } = projectSettings;

    const cssVars = `
      :root {
        --color-primary: ${colors.primary || '#3b82f6'};
        --color-secondary: ${colors.secondary || '#64748b'};
        --color-accent: ${colors.accent || '#f59e0b'};
        --color-background: ${colors.background || '#ffffff'};
        --color-text: ${colors.text || '#1f2937'};
        --color-text-light: ${colors.textLight || '#6b7280'};
        --color-border: ${colors.border || '#e5e7eb'};
        --font-heading: ${fonts.heading || "'Inter', sans-serif"};
        --font-body: ${fonts.body || "'Inter', sans-serif"};
        --spacing-sm: ${spacing.sm || '8px'};
        --spacing-md: ${spacing.md || '16px'};
        --spacing-lg: ${spacing.lg || '32px'};
        --spacing-xl: ${spacing.xl || '64px'};
        --radius-sm: ${spacing.radiusSm || '4px'};
        --radius-md: ${spacing.radiusMd || '8px'};
        --radius-lg: ${spacing.radiusLg || '16px'};
      }
    `;

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
    <div id="gjs" ref={editorRef} className="h-full" />
  );
}

export default GrapesEditor;
