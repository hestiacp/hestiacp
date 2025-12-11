/**
 * ===========================================
 * Gestionnaire de pages
 * ===========================================
 * 
 * Permet de :
 * - Voir la liste des pages du projet
 * - Ajouter une nouvelle page
 * - Supprimer une page
 * - Dupliquer une page
 * - Changer la page courante
 */

import { useState } from 'react';
import {
  Plus,
  Trash2,
  Copy,
  Home,
  FileText,
  MoreVertical,
  GripVertical
} from 'lucide-react';

function PageManager({
  pages,
  currentPage,
  onPageSelect,
  onAddPage,
  onDeletePage,
  onDuplicatePage
}) {
  const [showAddModal, setShowAddModal] = useState(false);
  const [newPageName, setNewPageName] = useState('');
  const [menuOpenId, setMenuOpenId] = useState(null);

  /**
   * Gère la création d'une nouvelle page
   */
  const handleAddPage = () => {
    if (newPageName.trim()) {
      onAddPage(newPageName.trim());
      setNewPageName('');
      setShowAddModal(false);
    }
  };

  /**
   * Gère la soumission du formulaire avec Entrée
   */
  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      handleAddPage();
    } else if (e.key === 'Escape') {
      setShowAddModal(false);
      setNewPageName('');
    }
  };

  return (
    <div className="page-manager">
      {/* Header avec bouton d'ajout */}
      <div className="page-manager-header">
        <h3 className="page-manager-title">Pages ({pages.length})</h3>
        <button
          onClick={() => setShowAddModal(true)}
          className="btn-icon"
          title="Ajouter une page"
        >
          <Plus size={18} />
        </button>
      </div>

      {/* Liste des pages */}
      <div className="page-list">
        {pages.map((page) => (
          <div
            key={page.id}
            className={`page-item ${currentPage?.id === page.id ? 'active' : ''}`}
            onClick={() => onPageSelect(page)}
          >
            {/* Icône de drag (futur: drag & drop) */}
            <div className="flex items-center gap-2">
              <GripVertical size={14} className="text-gray-500 cursor-grab" />
              
              {/* Icône de page */}
              {page.is_homepage ? (
                <Home size={16} className="text-yellow-500" />
              ) : (
                <FileText size={16} className="text-gray-400" />
              )}
            </div>

            {/* Infos de la page */}
            <div className="page-item-info flex-1">
              <div className="page-item-name">
                {page.name}
                {page.is_homepage && (
                  <span className="page-item-badge ml-2">Accueil</span>
                )}
              </div>
              <div className="page-item-slug">/{page.slug}</div>
            </div>

            {/* Menu d'actions */}
            <div className="page-item-actions relative">
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  setMenuOpenId(menuOpenId === page.id ? null : page.id);
                }}
                className="btn-icon"
              >
                <MoreVertical size={16} />
              </button>

              {/* Dropdown menu */}
              {menuOpenId === page.id && (
                <>
                  <div
                    className="fixed inset-0 z-40"
                    onClick={(e) => {
                      e.stopPropagation();
                      setMenuOpenId(null);
                    }}
                  />
                  <div className="absolute right-0 top-full mt-1 w-40 bg-editor-panel rounded-lg shadow-lg border border-editor-border z-50">
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        onDuplicatePage(page.id);
                        setMenuOpenId(null);
                      }}
                      className="w-full flex items-center gap-2 px-3 py-2 text-left text-gray-300 hover:bg-editor-bg rounded-t-lg transition-colors"
                    >
                      <Copy size={14} />
                      Dupliquer
                    </button>
                    
                    {!page.is_homepage && pages.length > 1 && (
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          if (confirm('Supprimer cette page ?')) {
                            onDeletePage(page.id);
                          }
                          setMenuOpenId(null);
                        }}
                        className="w-full flex items-center gap-2 px-3 py-2 text-left text-red-400 hover:bg-editor-bg rounded-b-lg transition-colors"
                      >
                        <Trash2 size={14} />
                        Supprimer
                      </button>
                    )}
                  </div>
                </>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Modal d'ajout de page */}
      {showAddModal && (
        <div className="modal-overlay" onClick={() => setShowAddModal(false)}>
          <div className="modal" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h3 className="modal-title">Nouvelle page</h3>
              <button
                onClick={() => setShowAddModal(false)}
                className="btn-icon"
              >
                ×
              </button>
            </div>

            <div className="modal-body">
              <div className="form-group">
                <label className="form-label" htmlFor="page-name">
                  Nom de la page
                </label>
                <input
                  type="text"
                  id="page-name"
                  className="form-input"
                  placeholder="Ex: À propos, Contact, Services..."
                  value={newPageName}
                  onChange={(e) => setNewPageName(e.target.value)}
                  onKeyDown={handleKeyDown}
                  autoFocus
                />
                <p className="text-xs text-gray-500 mt-1">
                  Le slug URL sera généré automatiquement
                </p>
              </div>
            </div>

            <div className="modal-footer">
              <button
                onClick={() => setShowAddModal(false)}
                className="btn btn-secondary"
              >
                Annuler
              </button>
              <button
                onClick={handleAddPage}
                disabled={!newPageName.trim()}
                className="btn btn-primary"
              >
                Créer la page
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default PageManager;
