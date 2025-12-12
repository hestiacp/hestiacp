/**
 * ===========================================
 * Panneau Scripts & Analytics
 * ===========================================
 * 
 * Permet d'ajouter :
 * - Google Analytics
 * - Facebook Pixel
 * - Scripts personnalisés (head/body)
 * - Meta tags custom
 */

import { useState, useEffect } from 'react';
import { Code, BarChart2, Save, X, Plus, Trash2, AlertCircle } from 'lucide-react';

function ScriptsPanel({ settings, onSave, onClose }) {
  const [scripts, setScripts] = useState({
    googleAnalyticsId: '',
    facebookPixelId: '',
    headScripts: '',
    bodyStartScripts: '',
    bodyEndScripts: '',
    customMeta: []
  });

  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState('analytics');

  useEffect(() => {
    if (settings?.scripts) {
      setScripts(prev => ({
        ...prev,
        ...settings.scripts
      }));
    }
  }, [settings]);

  const handleChange = (key, value) => {
    setScripts(prev => ({ ...prev, [key]: value }));
  };

  const addCustomMeta = () => {
    setScripts(prev => ({
      ...prev,
      customMeta: [...prev.customMeta, { name: '', content: '' }]
    }));
  };

  const updateCustomMeta = (index, field, value) => {
    setScripts(prev => ({
      ...prev,
      customMeta: prev.customMeta.map((meta, i) => 
        i === index ? { ...meta, [field]: value } : meta
      )
    }));
  };

  const removeCustomMeta = (index) => {
    setScripts(prev => ({
      ...prev,
      customMeta: prev.customMeta.filter((_, i) => i !== index)
    }));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await onSave({ scripts });
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="scripts-panel">
      <div className="panel-header">
        <h3><Code size={18} /> Scripts & Analytics</h3>
        <button onClick={onClose} className="close-btn">
          <X size={18} />
        </button>
      </div>

      {/* Tabs */}
      <div className="panel-tabs">
        <button 
          className={`tab ${activeTab === 'analytics' ? 'active' : ''}`}
          onClick={() => setActiveTab('analytics')}
        >
          <BarChart2 size={14} /> Analytics
        </button>
        <button 
          className={`tab ${activeTab === 'scripts' ? 'active' : ''}`}
          onClick={() => setActiveTab('scripts')}
        >
          <Code size={14} /> Scripts
        </button>
      </div>

      <div className="panel-content">
        {/* Onglet Analytics */}
        {activeTab === 'analytics' && (
          <div className="tab-content">
            {/* Google Analytics */}
            <div className="analytics-card">
              <div className="card-header">
                <img src="https://www.gstatic.com/analytics-suite/header/suite/v2/ic_analytics.svg" alt="GA" className="card-icon" />
                <div>
                  <h4>Google Analytics</h4>
                  <p>Suivez vos visiteurs avec GA4</p>
                </div>
              </div>
              <div className="form-group">
                <label>Measurement ID</label>
                <input
                  type="text"
                  value={scripts.googleAnalyticsId}
                  onChange={(e) => handleChange('googleAnalyticsId', e.target.value)}
                  placeholder="G-XXXXXXXXXX"
                />
                <small>Trouvez-le dans Google Analytics &gt; Admin &gt; Data Streams</small>
              </div>
            </div>

            {/* Facebook Pixel */}
            <div className="analytics-card">
              <div className="card-header">
                <div className="fb-icon">f</div>
                <div>
                  <h4>Facebook Pixel</h4>
                  <p>Mesurez vos conversions Facebook/Meta</p>
                </div>
              </div>
              <div className="form-group">
                <label>Pixel ID</label>
                <input
                  type="text"
                  value={scripts.facebookPixelId}
                  onChange={(e) => handleChange('facebookPixelId', e.target.value)}
                  placeholder="123456789012345"
                />
                <small>Trouvez-le dans Meta Events Manager</small>
              </div>
            </div>

            {/* Meta tags personnalisés */}
            <div className="analytics-card">
              <div className="card-header">
                <div className="meta-icon">&lt;/&gt;</div>
                <div>
                  <h4>Meta Tags personnalisés</h4>
                  <p>Ajoutez des balises meta supplémentaires</p>
                </div>
              </div>
              
              <div className="custom-meta-list">
                {scripts.customMeta.map((meta, index) => (
                  <div key={index} className="custom-meta-item">
                    <input
                      type="text"
                      value={meta.name}
                      onChange={(e) => updateCustomMeta(index, 'name', e.target.value)}
                      placeholder="name/property"
                    />
                    <input
                      type="text"
                      value={meta.content}
                      onChange={(e) => updateCustomMeta(index, 'content', e.target.value)}
                      placeholder="content"
                    />
                    <button onClick={() => removeCustomMeta(index)} className="remove-btn">
                      <Trash2 size={16} />
                    </button>
                  </div>
                ))}
              </div>
              
              <button onClick={addCustomMeta} className="add-meta-btn">
                <Plus size={16} /> Ajouter une meta tag
              </button>
            </div>
          </div>
        )}

        {/* Onglet Scripts */}
        {activeTab === 'scripts' && (
          <div className="tab-content">
            <div className="warning-box">
              <AlertCircle size={18} />
              <span>Les scripts personnalisés peuvent affecter les performances et la sécurité de votre site.</span>
            </div>

            {/* Scripts Head */}
            <div className="form-group">
              <label>Scripts dans &lt;head&gt;</label>
              <textarea
                value={scripts.headScripts}
                onChange={(e) => handleChange('headScripts', e.target.value)}
                placeholder="<!-- Scripts à insérer dans le <head> -->"
                rows={6}
              />
              <small>Idéal pour: CSS externes, polices, scripts de tracking</small>
            </div>

            {/* Scripts Body Start */}
            <div className="form-group">
              <label>Scripts après &lt;body&gt;</label>
              <textarea
                value={scripts.bodyStartScripts}
                onChange={(e) => handleChange('bodyStartScripts', e.target.value)}
                placeholder="<!-- Scripts juste après l'ouverture du <body> -->"
                rows={4}
              />
              <small>Idéal pour: Google Tag Manager (noscript), pixels de tracking</small>
            </div>

            {/* Scripts Body End */}
            <div className="form-group">
              <label>Scripts avant &lt;/body&gt;</label>
              <textarea
                value={scripts.bodyEndScripts}
                onChange={(e) => handleChange('bodyEndScripts', e.target.value)}
                placeholder="<!-- Scripts avant la fermeture du </body> -->"
                rows={6}
              />
              <small>Idéal pour: JavaScript tiers, chatbots, widgets</small>
            </div>
          </div>
        )}
      </div>

      <div className="panel-footer">
        <button onClick={onClose} className="btn-secondary">
          Annuler
        </button>
        <button onClick={handleSave} className="btn-primary" disabled={saving}>
          <Save size={16} />
          {saving ? 'Enregistrement...' : 'Enregistrer'}
        </button>
      </div>

      <style>{`
        .scripts-panel {
          position: fixed;
          right: 0;
          top: 56px;
          bottom: 0;
          width: 420px;
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
        
        .panel-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .panel-header h3 {
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
        }
        
        .panel-tabs {
          display: flex;
          border-bottom: 1px solid #0f3460;
        }
        
        .tab {
          flex: 1;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          padding: 12px;
          background: none;
          border: none;
          color: #9ca3af;
          font-size: 13px;
          cursor: pointer;
        }
        
        .tab.active {
          color: #3b82f6;
          border-bottom: 2px solid #3b82f6;
        }
        
        .panel-content {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
        }
        
        .tab-content {
          animation: fadeIn 0.2s;
        }
        
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        
        .analytics-card {
          background: rgba(255,255,255,0.03);
          border: 1px solid #0f3460;
          border-radius: 12px;
          padding: 20px;
          margin-bottom: 16px;
        }
        
        .card-header {
          display: flex;
          gap: 12px;
          margin-bottom: 16px;
        }
        
        .card-icon {
          width: 40px;
          height: 40px;
        }
        
        .fb-icon {
          width: 40px;
          height: 40px;
          background: #1877f2;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 24px;
        }
        
        .meta-icon {
          width: 40px;
          height: 40px;
          background: #6366f1;
          border-radius: 8px;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 14px;
        }
        
        .card-header h4 {
          color: #fff;
          margin: 0 0 4px 0;
          font-size: 15px;
        }
        
        .card-header p {
          color: #9ca3af;
          margin: 0;
          font-size: 13px;
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
          font-family: monospace;
        }
        
        .form-group textarea {
          resize: vertical;
          min-height: 80px;
        }
        
        .form-group small {
          display: block;
          color: #6b7280;
          font-size: 12px;
          margin-top: 6px;
        }
        
        .warning-box {
          display: flex;
          align-items: center;
          gap: 12px;
          padding: 12px 16px;
          background: rgba(245, 158, 11, 0.1);
          border: 1px solid rgba(245, 158, 11, 0.3);
          border-radius: 8px;
          color: #f59e0b;
          font-size: 13px;
          margin-bottom: 20px;
        }
        
        .custom-meta-list {
          display: flex;
          flex-direction: column;
          gap: 8px;
          margin-bottom: 12px;
        }
        
        .custom-meta-item {
          display: flex;
          gap: 8px;
        }
        
        .custom-meta-item input {
          flex: 1;
          padding: 8px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-size: 13px;
        }
        
        .remove-btn {
          padding: 8px;
          background: rgba(239, 68, 68, 0.1);
          border: none;
          border-radius: 6px;
          color: #ef4444;
          cursor: pointer;
        }
        
        .add-meta-btn {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          width: 100%;
          padding: 10px;
          background: rgba(255,255,255,0.05);
          border: 1px dashed #0f3460;
          border-radius: 6px;
          color: #9ca3af;
          font-size: 13px;
          cursor: pointer;
        }
        
        .add-meta-btn:hover {
          background: rgba(255,255,255,0.08);
          color: #fff;
        }
        
        .panel-footer {
          display: flex;
          justify-content: flex-end;
          gap: 12px;
          padding: 16px;
          border-top: 1px solid #0f3460;
        }
        
        .btn-secondary {
          padding: 10px 20px;
          background: rgba(255,255,255,0.1);
          border: none;
          border-radius: 6px;
          color: #e4e4e7;
          font-size: 14px;
          cursor: pointer;
        }
        
        .btn-primary {
          display: flex;
          align-items: center;
          gap: 6px;
          padding: 10px 20px;
          background: #3b82f6;
          border: none;
          border-radius: 6px;
          color: white;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
        }
        
        .btn-primary:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
      `}</style>
    </div>
  );
}

export default ScriptsPanel;
