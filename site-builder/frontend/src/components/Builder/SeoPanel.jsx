/**
 * ===========================================
 * Panneau SEO pour les pages
 * ===========================================
 * 
 * Permet de configurer :
 * - Meta title
 * - Meta description
 * - URL/slug
 * - OG tags
 */

import { useState, useEffect } from 'react';
import { Search, FileText, Image, Globe, Save, X } from 'lucide-react';

function SeoPanel({ page, onSave, onClose }) {
  const [seo, setSeo] = useState({
    title: '',
    description: '',
    keywords: '',
    og_title: '',
    og_description: '',
    og_image: ''
  });
  const [slug, setSlug] = useState('');
  const [showInMenu, setShowInMenu] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (page) {
      setSeo({
        title: page.seo?.title || '',
        description: page.seo?.description || '',
        keywords: page.seo?.keywords || '',
        og_title: page.seo?.og_title || '',
        og_description: page.seo?.og_description || '',
        og_image: page.seo?.og_image || ''
      });
      setSlug(page.slug || '');
      setShowInMenu(page.show_in_menu !== false);
    }
  }, [page]);

  const handleSave = async () => {
    setSaving(true);
    try {
      await onSave({
        slug,
        show_in_menu: showInMenu,
        seo
      });
    } finally {
      setSaving(false);
    }
  };

  // Calcul de la qualité SEO
  const getSeoScore = () => {
    let score = 0;
    if (seo.title && seo.title.length >= 30 && seo.title.length <= 60) score += 25;
    else if (seo.title) score += 10;
    
    if (seo.description && seo.description.length >= 120 && seo.description.length <= 160) score += 25;
    else if (seo.description) score += 10;
    
    if (seo.keywords) score += 15;
    if (seo.og_title || seo.title) score += 15;
    if (seo.og_description || seo.description) score += 10;
    if (seo.og_image) score += 10;
    
    return score;
  };

  const score = getSeoScore();
  const scoreColor = score >= 80 ? '#10b981' : score >= 50 ? '#f59e0b' : '#ef4444';

  return (
    <div className="seo-panel">
      <div className="seo-panel-header">
        <h3><Search size={18} /> SEO & Métadonnées</h3>
        <button onClick={onClose} className="close-btn">
          <X size={18} />
        </button>
      </div>

      {/* Score SEO */}
      <div className="seo-score" style={{ borderColor: scoreColor }}>
        <div className="score-circle" style={{ backgroundColor: scoreColor }}>
          {score}%
        </div>
        <div className="score-text">
          <strong>Score SEO</strong>
          <span>{score >= 80 ? 'Excellent' : score >= 50 ? 'Moyen' : 'À améliorer'}</span>
        </div>
      </div>

      <div className="seo-panel-content">
        {/* URL/Slug */}
        <div className="form-group">
          <label><Globe size={14} /> URL de la page</label>
          <div className="slug-input">
            <span className="slug-prefix">/</span>
            <input
              type="text"
              value={slug}
              onChange={(e) => setSlug(e.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '-'))}
              placeholder="nom-de-page"
              disabled={page?.is_homepage}
            />
            <span className="slug-suffix">.html</span>
          </div>
          {page?.is_homepage && (
            <small className="hint">La page d'accueil utilise toujours "index.html"</small>
          )}
        </div>

        {/* Afficher dans le menu */}
        <div className="form-group checkbox-group">
          <label>
            <input
              type="checkbox"
              checked={showInMenu}
              onChange={(e) => setShowInMenu(e.target.checked)}
            />
            Afficher dans le menu de navigation
          </label>
        </div>

        <hr />

        {/* Meta Title */}
        <div className="form-group">
          <label><FileText size={14} /> Titre de la page (meta title)</label>
          <input
            type="text"
            value={seo.title}
            onChange={(e) => setSeo({ ...seo, title: e.target.value })}
            placeholder="Titre affiché dans les résultats Google"
            maxLength={70}
          />
          <div className="char-count" style={{ color: seo.title.length > 60 ? '#ef4444' : '#6b7280' }}>
            {seo.title.length}/60 caractères {seo.title.length >= 30 && seo.title.length <= 60 ? '✓' : ''}
          </div>
        </div>

        {/* Meta Description */}
        <div className="form-group">
          <label><FileText size={14} /> Description (meta description)</label>
          <textarea
            value={seo.description}
            onChange={(e) => setSeo({ ...seo, description: e.target.value })}
            placeholder="Description affichée dans les résultats de recherche"
            rows={3}
            maxLength={170}
          />
          <div className="char-count" style={{ color: seo.description.length > 160 ? '#ef4444' : '#6b7280' }}>
            {seo.description.length}/160 caractères {seo.description.length >= 120 && seo.description.length <= 160 ? '✓' : ''}
          </div>
        </div>

        {/* Keywords */}
        <div className="form-group">
          <label>Mots-clés (séparés par des virgules)</label>
          <input
            type="text"
            value={seo.keywords}
            onChange={(e) => setSeo({ ...seo, keywords: e.target.value })}
            placeholder="mot-clé1, mot-clé2, mot-clé3"
          />
        </div>

        <hr />

        <h4>Open Graph (réseaux sociaux)</h4>

        {/* OG Title */}
        <div className="form-group">
          <label>Titre OG</label>
          <input
            type="text"
            value={seo.og_title}
            onChange={(e) => setSeo({ ...seo, og_title: e.target.value })}
            placeholder={seo.title || "Titre pour Facebook/Twitter"}
          />
          <small className="hint">Laissez vide pour utiliser le meta title</small>
        </div>

        {/* OG Description */}
        <div className="form-group">
          <label>Description OG</label>
          <textarea
            value={seo.og_description}
            onChange={(e) => setSeo({ ...seo, og_description: e.target.value })}
            placeholder={seo.description || "Description pour les réseaux sociaux"}
            rows={2}
          />
        </div>

        {/* OG Image */}
        <div className="form-group">
          <label><Image size={14} /> Image OG (1200x630 recommandé)</label>
          <input
            type="text"
            value={seo.og_image}
            onChange={(e) => setSeo({ ...seo, og_image: e.target.value })}
            placeholder="https://example.com/image.jpg"
          />
        </div>

        {/* Preview Google */}
        <div className="seo-preview">
          <h5>Aperçu Google</h5>
          <div className="google-preview">
            <div className="google-title">{seo.title || page?.name || 'Titre de la page'}</div>
            <div className="google-url">example.com/{slug || 'page'}</div>
            <div className="google-description">
              {seo.description || 'Ajoutez une meta description pour améliorer votre référencement.'}
            </div>
          </div>
        </div>
      </div>

      <div className="seo-panel-footer">
        <button onClick={onClose} className="btn-secondary">
          Annuler
        </button>
        <button onClick={handleSave} className="btn-primary" disabled={saving}>
          <Save size={16} />
          {saving ? 'Enregistrement...' : 'Enregistrer'}
        </button>
      </div>

      <style>{`
        .seo-panel {
          position: fixed;
          right: 0;
          top: 56px;
          bottom: 0;
          width: 400px;
          background: #16213e;
          border-left: 1px solid #0f3460;
          display: flex;
          flex-direction: column;
          z-index: 100;
          animation: slideIn 0.2s ease;
        }
        
        @keyframes slideIn {
          from { transform: translateX(100%); }
          to { transform: translateX(0); }
        }
        
        .seo-panel-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .seo-panel-header h3 {
          display: flex;
          align-items: center;
          gap: 8px;
          margin: 0;
          color: #fff;
          font-size: 16px;
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
        
        .seo-score {
          display: flex;
          align-items: center;
          gap: 16px;
          padding: 16px;
          margin: 16px;
          background: rgba(255,255,255,0.05);
          border-radius: 8px;
          border-left: 4px solid;
        }
        
        .score-circle {
          width: 50px;
          height: 50px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 14px;
        }
        
        .score-text {
          display: flex;
          flex-direction: column;
          color: #e4e4e7;
        }
        
        .score-text span {
          color: #9ca3af;
          font-size: 13px;
        }
        
        .seo-panel-content {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
        }
        
        .form-group {
          margin-bottom: 16px;
        }
        
        .form-group label {
          display: flex;
          align-items: center;
          gap: 6px;
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
        
        .form-group input:focus,
        .form-group textarea:focus {
          outline: none;
          border-color: #3b82f6;
        }
        
        .form-group textarea {
          resize: vertical;
          min-height: 60px;
        }
        
        .slug-input {
          display: flex;
          align-items: center;
        }
        
        .slug-prefix,
        .slug-suffix {
          padding: 10px 8px;
          background: rgba(255,255,255,0.1);
          color: #9ca3af;
          font-size: 14px;
        }
        
        .slug-prefix {
          border-radius: 6px 0 0 6px;
          border: 1px solid #0f3460;
          border-right: none;
        }
        
        .slug-suffix {
          border-radius: 0 6px 6px 0;
          border: 1px solid #0f3460;
          border-left: none;
        }
        
        .slug-input input {
          border-radius: 0;
          flex: 1;
        }
        
        .char-count {
          font-size: 12px;
          text-align: right;
          margin-top: 4px;
        }
        
        .hint {
          color: #6b7280;
          font-size: 12px;
          margin-top: 4px;
        }
        
        .checkbox-group label {
          cursor: pointer;
        }
        
        .checkbox-group input[type="checkbox"] {
          width: auto;
          margin-right: 8px;
        }
        
        hr {
          border: none;
          border-top: 1px solid #0f3460;
          margin: 20px 0;
        }
        
        h4 {
          color: #e4e4e7;
          font-size: 14px;
          margin: 0 0 16px 0;
        }
        
        .seo-preview {
          margin-top: 20px;
          padding: 16px;
          background: rgba(255,255,255,0.03);
          border-radius: 8px;
        }
        
        .seo-preview h5 {
          color: #9ca3af;
          font-size: 12px;
          margin: 0 0 12px 0;
          text-transform: uppercase;
        }
        
        .google-preview {
          background: #fff;
          padding: 12px;
          border-radius: 8px;
        }
        
        .google-title {
          color: #1a0dab;
          font-size: 18px;
          font-weight: 500;
          margin-bottom: 4px;
          cursor: pointer;
        }
        
        .google-title:hover {
          text-decoration: underline;
        }
        
        .google-url {
          color: #006621;
          font-size: 14px;
          margin-bottom: 4px;
        }
        
        .google-description {
          color: #545454;
          font-size: 13px;
          line-height: 1.4;
        }
        
        .seo-panel-footer {
          display: flex;
          justify-content: flex-end;
          gap: 12px;
          padding: 16px;
          border-top: 1px solid #0f3460;
        }
        
        .btn-secondary,
        .btn-primary {
          display: flex;
          align-items: center;
          gap: 6px;
          padding: 10px 20px;
          border-radius: 6px;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
          border: none;
        }
        
        .btn-secondary {
          background: rgba(255,255,255,0.1);
          color: #e4e4e7;
        }
        
        .btn-primary {
          background: #3b82f6;
          color: white;
        }
        
        .btn-primary:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      `}</style>
    </div>
  );
}

export default SeoPanel;
