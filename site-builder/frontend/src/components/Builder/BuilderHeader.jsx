/**
 * ===========================================
 * Header du Builder
 * ===========================================
 * 
 * Barre supérieure avec :
 * - Logo et nom du projet
 * - Sélecteur de device (desktop/tablet/mobile)
 * - Boutons d'actions (preview, save, publish)
 */

import { useState } from 'react';
import {
  Save,
  Upload,
  Eye,
  Monitor,
  Tablet,
  Smartphone,
  Undo,
  Redo,
  Settings,
  LogOut,
  Loader2
} from 'lucide-react';

function BuilderHeader({
  project,
  currentPage,
  onSave,
  onPublish,
  saving,
  publishing,
  user
}) {
  const [activeDevice, setActiveDevice] = useState('desktop');
  const [showUserMenu, setShowUserMenu] = useState(false);

  /**
   * Change le device preview dans GrapesJS
   */
  const handleDeviceChange = (device) => {
    setActiveDevice(device);
    
    // Accéder à l'éditeur GrapesJS via l'instance globale
    const editor = window.grapesEditor;
    if (editor) {
      const deviceMap = {
        desktop: 'Desktop',
        tablet: 'Tablet',
        mobile: 'Mobile portrait'
      };
      editor.setDevice(deviceMap[device]);
    }
  };

  /**
   * Ouvre un aperçu dans un nouvel onglet
   */
  const handlePreview = () => {
    const editor = window.grapesEditor;
    if (editor) {
      // Ouvrir la prévisualisation GrapesJS
      const html = editor.getHtml();
      const css = editor.getCss();
      
      const previewContent = `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Aperçu - ${currentPage?.name || 'Page'}</title>
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
          <style>
            body { font-family: 'Inter', sans-serif; margin: 0; }
            ${css}
          </style>
        </head>
        <body>
          ${html}
        </body>
        </html>
      `;
      
      const blob = new Blob([previewContent], { type: 'text/html' });
      const url = URL.createObjectURL(blob);
      window.open(url, '_blank');
    }
  };

  /**
   * Undo dans GrapesJS
   */
  const handleUndo = () => {
    const editor = window.grapesEditor;
    if (editor) {
      editor.UndoManager.undo();
    }
  };

  /**
   * Redo dans GrapesJS
   */
  const handleRedo = () => {
    const editor = window.grapesEditor;
    if (editor) {
      editor.UndoManager.redo();
    }
  };

  return (
    <header className="builder-header">
      {/* Section gauche - Logo et projet */}
      <div className="builder-header-left">
        <div className="builder-logo">
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect width="24" height="24" rx="4" fill="#3b82f6" />
            <path
              d="M7 8h10M7 12h10M7 16h6"
              stroke="white"
              strokeWidth="2"
              strokeLinecap="round"
            />
          </svg>
          <span>Site Builder</span>
        </div>
        
        <div className="builder-project-name">
          {project?.project_name || project?.domain_name}
          {currentPage && (
            <span className="text-gray-500 mx-2">/</span>
          )}
          {currentPage?.name}
        </div>
      </div>

      {/* Section centrale - Contrôles de device et undo/redo */}
      <div className="builder-header-center">
        {/* Undo/Redo */}
        <div className="flex items-center gap-1 mr-4">
          <button
            onClick={handleUndo}
            className="btn-icon"
            title="Annuler (Ctrl+Z)"
          >
            <Undo size={18} />
          </button>
          <button
            onClick={handleRedo}
            className="btn-icon"
            title="Rétablir (Ctrl+Y)"
          >
            <Redo size={18} />
          </button>
        </div>

        {/* Device selector */}
        <div className="flex items-center bg-editor-bg rounded-lg p-1">
          <button
            onClick={() => handleDeviceChange('desktop')}
            className={`btn-icon ${activeDevice === 'desktop' ? 'bg-primary-600 text-white' : ''}`}
            title="Bureau"
          >
            <Monitor size={18} />
          </button>
          <button
            onClick={() => handleDeviceChange('tablet')}
            className={`btn-icon ${activeDevice === 'tablet' ? 'bg-primary-600 text-white' : ''}`}
            title="Tablette"
          >
            <Tablet size={18} />
          </button>
          <button
            onClick={() => handleDeviceChange('mobile')}
            className={`btn-icon ${activeDevice === 'mobile' ? 'bg-primary-600 text-white' : ''}`}
            title="Mobile"
          >
            <Smartphone size={18} />
          </button>
        </div>
      </div>

      {/* Section droite - Actions */}
      <div className="builder-header-right">
        {/* Aperçu */}
        <button
          onClick={handlePreview}
          className="btn btn-secondary"
          title="Aperçu"
        >
          <Eye size={16} />
          <span className="hidden sm:inline">Aperçu</span>
        </button>

        {/* Sauvegarder */}
        <button
          onClick={onSave}
          disabled={saving}
          className="btn btn-secondary"
          title="Sauvegarder (Ctrl+S)"
        >
          {saving ? (
            <Loader2 size={16} className="animate-spin" />
          ) : (
            <Save size={16} />
          )}
          <span className="hidden sm:inline">
            {saving ? 'Sauvegarde...' : 'Sauvegarder'}
          </span>
        </button>

        {/* Publier */}
        <button
          onClick={onPublish}
          disabled={publishing}
          className="btn btn-success"
          title="Publier le site"
        >
          {publishing ? (
            <Loader2 size={16} className="animate-spin" />
          ) : (
            <Upload size={16} />
          )}
          <span className="hidden sm:inline">
            {publishing ? 'Publication...' : 'Publier'}
          </span>
        </button>

        {/* Menu utilisateur */}
        <div className="relative">
          <button
            onClick={() => setShowUserMenu(!showUserMenu)}
            className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-editor-panel transition-colors"
          >
            <div className="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium">
              {user?.hestia_username?.charAt(0).toUpperCase() || 'U'}
            </div>
          </button>

          {/* Dropdown menu */}
          {showUserMenu && (
            <>
              <div
                className="fixed inset-0 z-40"
                onClick={() => setShowUserMenu(false)}
              />
              <div className="absolute right-0 top-full mt-2 w-48 bg-editor-panel rounded-lg shadow-lg border border-editor-border z-50">
                <div className="p-3 border-b border-editor-border">
                  <div className="font-medium text-white">
                    {user?.hestia_username}
                  </div>
                  <div className="text-sm text-gray-400">
                    {user?.email || 'Utilisateur HestiaCP'}
                  </div>
                </div>
                
                <div className="p-2">
                  <button
                    className="w-full flex items-center gap-2 px-3 py-2 text-left text-gray-300 hover:bg-editor-bg rounded-lg transition-colors"
                  >
                    <Settings size={16} />
                    Paramètres
                  </button>
                  
                  <button
                    onClick={() => {
                      localStorage.removeItem('sitebuilder_token');
                      window.location.href = '/';
                    }}
                    className="w-full flex items-center gap-2 px-3 py-2 text-left text-red-400 hover:bg-editor-bg rounded-lg transition-colors"
                  >
                    <LogOut size={16} />
                    Déconnexion
                  </button>
                </div>
              </div>
            </>
          )}
        </div>
      </div>
    </header>
  );
}

export default BuilderHeader;
