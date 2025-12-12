/**
 * ===========================================
 * Sélecteur de Templates
 * ===========================================
 * 
 * Permet de :
 * - Choisir un template prédéfini
 * - Sauvegarder son projet comme template
 * - Importer des templates personnalisés
 */

import { useState } from 'react';
import { Layout, Plus, Download, Upload, X, Check, Star } from 'lucide-react';
import { listTemplates, getTemplate } from '../templates';

function TemplateSelector({ onSelectTemplate, onSaveAsTemplate, onClose }) {
  const [selectedTemplate, setSelectedTemplate] = useState(null);
  const [showSaveModal, setShowSaveModal] = useState(false);
  const [templateName, setTemplateName] = useState('');
  const [templateDesc, setTemplateDesc] = useState('');
  const [loading, setLoading] = useState(false);

  const templates = listTemplates();

  const handleSelectTemplate = (templateId) => {
    setSelectedTemplate(templateId);
  };

  const handleApplyTemplate = async () => {
    if (!selectedTemplate) return;
    
    setLoading(true);
    try {
      const template = getTemplate(selectedTemplate);
      if (template && onSelectTemplate) {
        await onSelectTemplate(template);
      }
    } finally {
      setLoading(false);
      onClose();
    }
  };

  const handleSaveAsTemplate = async () => {
    if (!templateName.trim()) return;
    
    setLoading(true);
    try {
      if (onSaveAsTemplate) {
        await onSaveAsTemplate({
          name: templateName,
          description: templateDesc
        });
      }
      setShowSaveModal(false);
      setTemplateName('');
      setTemplateDesc('');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="template-overlay">
      <div className="template-modal">
        <div className="template-header">
          <h2><Layout size={24} /> Templates de site</h2>
          <button onClick={onClose} className="close-btn">
            <X size={24} />
          </button>
        </div>

        <div className="template-content">
          {/* Actions */}
          <div className="template-actions">
            <button 
              className="action-btn save-btn"
              onClick={() => setShowSaveModal(true)}
            >
              <Download size={18} />
              Sauvegarder comme template
            </button>
          </div>

          {/* Liste des templates */}
          <div className="templates-grid">
            {templates.map(template => (
              <div 
                key={template.id}
                className={`template-card ${selectedTemplate === template.id ? 'selected' : ''}`}
                onClick={() => handleSelectTemplate(template.id)}
              >
                <div 
                  className="template-thumbnail"
                  style={{ backgroundImage: `url(${template.thumbnail})` }}
                >
                  {selectedTemplate === template.id && (
                    <div className="selected-badge">
                      <Check size={20} />
                    </div>
                  )}
                </div>
                <div className="template-info">
                  <h3>{template.name}</h3>
                  <p>{template.description}</p>
                </div>
              </div>
            ))}

            {/* Carte "Créer vide" */}
            <div 
              className={`template-card blank-card ${selectedTemplate === 'blank' ? 'selected' : ''}`}
              onClick={() => handleSelectTemplate('blank')}
            >
              <div className="template-thumbnail blank-thumbnail">
                <Plus size={40} />
              </div>
              <div className="template-info">
                <h3>Page vierge</h3>
                <p>Commencer de zéro</p>
              </div>
            </div>
          </div>
        </div>

        <div className="template-footer">
          <button onClick={onClose} className="btn-secondary">
            Annuler
          </button>
          <button 
            onClick={handleApplyTemplate} 
            className="btn-primary"
            disabled={!selectedTemplate || loading}
          >
            {loading ? 'Chargement...' : 'Utiliser ce template'}
          </button>
        </div>

        {/* Modal Save As Template */}
        {showSaveModal && (
          <div className="save-modal-overlay">
            <div className="save-modal">
              <h3>Sauvegarder comme template</h3>
              <p>Sauvegardez votre design actuel pour le réutiliser plus tard.</p>
              
              <div className="form-group">
                <label>Nom du template</label>
                <input
                  type="text"
                  value={templateName}
                  onChange={(e) => setTemplateName(e.target.value)}
                  placeholder="Ex: Mon site vitrine"
                />
              </div>
              
              <div className="form-group">
                <label>Description (optionnel)</label>
                <textarea
                  value={templateDesc}
                  onChange={(e) => setTemplateDesc(e.target.value)}
                  placeholder="Décrivez ce template..."
                  rows={3}
                />
              </div>

              <div className="save-modal-footer">
                <button onClick={() => setShowSaveModal(false)} className="btn-secondary">
                  Annuler
                </button>
                <button 
                  onClick={handleSaveAsTemplate} 
                  className="btn-primary"
                  disabled={!templateName.trim() || loading}
                >
                  {loading ? 'Enregistrement...' : 'Sauvegarder'}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>

      <style>{`
        .template-overlay {
          position: fixed;
          inset: 0;
          background: rgba(0,0,0,0.8);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 1000;
          animation: fadeIn 0.2s;
        }
        
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        
        .template-modal {
          background: #1a1a2e;
          border-radius: 16px;
          width: 90%;
          max-width: 1000px;
          max-height: 90vh;
          display: flex;
          flex-direction: column;
          animation: slideUp 0.3s;
        }
        
        @keyframes slideUp {
          from { transform: translateY(20px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }
        
        .template-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 24px;
          border-bottom: 1px solid #0f3460;
        }
        
        .template-header h2 {
          display: flex;
          align-items: center;
          gap: 12px;
          margin: 0;
          color: #fff;
          font-size: 20px;
        }
        
        .close-btn {
          background: none;
          border: none;
          color: #9ca3af;
          cursor: pointer;
          padding: 4px;
        }
        
        .close-btn:hover {
          color: #fff;
        }
        
        .template-content {
          flex: 1;
          overflow-y: auto;
          padding: 24px;
        }
        
        .template-actions {
          display: flex;
          gap: 12px;
          margin-bottom: 24px;
        }
        
        .action-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 12px 20px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 8px;
          color: #e4e4e7;
          font-size: 14px;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .action-btn:hover {
          background: rgba(255,255,255,0.1);
          border-color: #3b82f6;
        }
        
        .templates-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
          gap: 20px;
        }
        
        .template-card {
          background: rgba(255,255,255,0.03);
          border: 2px solid transparent;
          border-radius: 12px;
          overflow: hidden;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .template-card:hover {
          background: rgba(255,255,255,0.06);
          border-color: rgba(255,255,255,0.1);
        }
        
        .template-card.selected {
          border-color: #3b82f6;
          background: rgba(59,130,246,0.1);
        }
        
        .template-thumbnail {
          height: 160px;
          background: #0f3460;
          background-size: cover;
          background-position: center;
          position: relative;
        }
        
        .selected-badge {
          position: absolute;
          top: 12px;
          right: 12px;
          width: 32px;
          height: 32px;
          background: #3b82f6;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
        }
        
        .blank-thumbnail {
          display: flex;
          align-items: center;
          justify-content: center;
          color: #6b7280;
          border: 2px dashed #374151;
          background: transparent;
        }
        
        .template-info {
          padding: 16px;
        }
        
        .template-info h3 {
          color: #fff;
          font-size: 16px;
          margin: 0 0 6px 0;
        }
        
        .template-info p {
          color: #9ca3af;
          font-size: 13px;
          margin: 0;
        }
        
        .template-footer {
          display: flex;
          justify-content: flex-end;
          gap: 12px;
          padding: 20px 24px;
          border-top: 1px solid #0f3460;
        }
        
        .btn-secondary {
          padding: 12px 24px;
          background: rgba(255,255,255,0.1);
          border: none;
          border-radius: 8px;
          color: #e4e4e7;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
        }
        
        .btn-primary {
          padding: 12px 24px;
          background: #3b82f6;
          border: none;
          border-radius: 8px;
          color: white;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
        }
        
        .btn-primary:disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }
        
        /* Save Modal */
        .save-modal-overlay {
          position: absolute;
          inset: 0;
          background: rgba(0,0,0,0.7);
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 16px;
        }
        
        .save-modal {
          background: #16213e;
          border-radius: 12px;
          padding: 24px;
          width: 90%;
          max-width: 400px;
        }
        
        .save-modal h3 {
          color: #fff;
          margin: 0 0 8px 0;
        }
        
        .save-modal > p {
          color: #9ca3af;
          font-size: 14px;
          margin: 0 0 20px 0;
        }
        
        .form-group {
          margin-bottom: 16px;
        }
        
        .form-group label {
          display: block;
          color: #e4e4e7;
          font-size: 13px;
          margin-bottom: 6px;
        }
        
        .form-group input,
        .form-group textarea {
          width: 100%;
          padding: 10px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-size: 14px;
        }
        
        .form-group textarea {
          resize: vertical;
        }
        
        .save-modal-footer {
          display: flex;
          justify-content: flex-end;
          gap: 12px;
          margin-top: 20px;
        }
      `}</style>
    </div>
  );
}

export default TemplateSelector;
