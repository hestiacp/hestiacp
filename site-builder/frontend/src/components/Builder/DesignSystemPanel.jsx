/**
 * ===========================================
 * Panneau Design System
 * ===========================================
 * 
 * Permet de configurer :
 * - Palette de couleurs globale
 * - Typographie (fonts)
 * - Espacements
 * - Bordures et ombres
 */

import { useState, useEffect } from 'react';
import { Palette, Type, Settings, Save, X, RotateCcw } from 'lucide-react';

// Presets de couleurs prédéfinis
const colorPresets = {
  blue: {
    name: 'Bleu Professionnel',
    primary: '#3b82f6',
    secondary: '#64748b',
    accent: '#f59e0b',
    background: '#ffffff',
    text: '#1f2937'
  },
  green: {
    name: 'Vert Nature',
    primary: '#10b981',
    secondary: '#6b7280',
    accent: '#8b5cf6',
    background: '#f0fdf4',
    text: '#166534'
  },
  purple: {
    name: 'Violet Créatif',
    primary: '#8b5cf6',
    secondary: '#64748b',
    accent: '#ec4899',
    background: '#faf5ff',
    text: '#1f2937'
  },
  dark: {
    name: 'Mode Sombre',
    primary: '#3b82f6',
    secondary: '#94a3b8',
    accent: '#f59e0b',
    background: '#0f172a',
    text: '#f8fafc'
  },
  coral: {
    name: 'Corail Moderne',
    primary: '#f43f5e',
    secondary: '#64748b',
    accent: '#0ea5e9',
    background: '#fff1f2',
    text: '#1f2937'
  },
  gold: {
    name: 'Or Luxe',
    primary: '#d4a574',
    secondary: '#1e3a5f',
    accent: '#c9a227',
    background: '#1a1a1a',
    text: '#ffffff'
  }
};

// Fonts Google disponibles
const fontOptions = [
  { name: 'Inter', value: "'Inter', sans-serif" },
  { name: 'Roboto', value: "'Roboto', sans-serif" },
  { name: 'Open Sans', value: "'Open Sans', sans-serif" },
  { name: 'Montserrat', value: "'Montserrat', sans-serif" },
  { name: 'Poppins', value: "'Poppins', sans-serif" },
  { name: 'Playfair Display', value: "'Playfair Display', serif" },
  { name: 'Georgia', value: "Georgia, serif" },
  { name: 'Lato', value: "'Lato', sans-serif" }
];

function DesignSystemPanel({ settings, onSave, onClose }) {
  const [design, setDesign] = useState({
    colors: {
      primary: '#3b82f6',
      secondary: '#64748b',
      accent: '#f59e0b',
      background: '#ffffff',
      text: '#1f2937',
      textLight: '#6b7280',
      border: '#e5e7eb'
    },
    fonts: {
      heading: "'Inter', sans-serif",
      body: "'Inter', sans-serif"
    },
    spacing: {
      sm: '8px',
      md: '16px',
      lg: '32px',
      xl: '64px',
      radiusSm: '4px',
      radiusMd: '8px',
      radiusLg: '16px'
    }
  });

  const [activeTab, setActiveTab] = useState('colors');
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (settings) {
      setDesign(prev => ({
        colors: { ...prev.colors, ...settings.colors },
        fonts: { ...prev.fonts, ...settings.fonts },
        spacing: { ...prev.spacing, ...settings.spacing }
      }));
    }
  }, [settings]);

  const handleColorChange = (key, value) => {
    setDesign(prev => ({
      ...prev,
      colors: { ...prev.colors, [key]: value }
    }));
  };

  const handleFontChange = (key, value) => {
    setDesign(prev => ({
      ...prev,
      fonts: { ...prev.fonts, [key]: value }
    }));
  };

  const handleSpacingChange = (key, value) => {
    setDesign(prev => ({
      ...prev,
      spacing: { ...prev.spacing, [key]: value }
    }));
  };

  const applyPreset = (presetId) => {
    const preset = colorPresets[presetId];
    if (preset) {
      setDesign(prev => ({
        ...prev,
        colors: {
          ...prev.colors,
          primary: preset.primary,
          secondary: preset.secondary,
          accent: preset.accent,
          background: preset.background,
          text: preset.text
        }
      }));
    }
  };

  const resetToDefaults = () => {
    setDesign({
      colors: {
        primary: '#3b82f6',
        secondary: '#64748b',
        accent: '#f59e0b',
        background: '#ffffff',
        text: '#1f2937',
        textLight: '#6b7280',
        border: '#e5e7eb'
      },
      fonts: {
        heading: "'Inter', sans-serif",
        body: "'Inter', sans-serif"
      },
      spacing: {
        sm: '8px',
        md: '16px',
        lg: '32px',
        xl: '64px',
        radiusSm: '4px',
        radiusMd: '8px',
        radiusLg: '16px'
      }
    });
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await onSave(design);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="design-panel">
      <div className="design-panel-header">
        <h3><Palette size={18} /> Design System</h3>
        <button onClick={onClose} className="close-btn">
          <X size={18} />
        </button>
      </div>

      {/* Tabs */}
      <div className="design-tabs">
        <button 
          className={`tab ${activeTab === 'colors' ? 'active' : ''}`}
          onClick={() => setActiveTab('colors')}
        >
          <Palette size={14} /> Couleurs
        </button>
        <button 
          className={`tab ${activeTab === 'fonts' ? 'active' : ''}`}
          onClick={() => setActiveTab('fonts')}
        >
          <Type size={14} /> Typo
        </button>
        <button 
          className={`tab ${activeTab === 'spacing' ? 'active' : ''}`}
          onClick={() => setActiveTab('spacing')}
        >
          <Settings size={14} /> Espaces
        </button>
      </div>

      <div className="design-panel-content">
        {/* Onglet Couleurs */}
        {activeTab === 'colors' && (
          <div className="tab-content">
            {/* Presets */}
            <div className="presets-section">
              <label>Palettes prédéfinies</label>
              <div className="presets-grid">
                {Object.entries(colorPresets).map(([id, preset]) => (
                  <button
                    key={id}
                    className="preset-btn"
                    onClick={() => applyPreset(id)}
                    title={preset.name}
                  >
                    <div className="preset-colors">
                      <span style={{ background: preset.primary }}></span>
                      <span style={{ background: preset.secondary }}></span>
                      <span style={{ background: preset.accent }}></span>
                    </div>
                    <span className="preset-name">{preset.name}</span>
                  </button>
                ))}
              </div>
            </div>

            <hr />

            {/* Couleurs individuelles */}
            <div className="color-inputs">
              <div className="color-input">
                <label>Couleur principale</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.primary}
                    onChange={(e) => handleColorChange('primary', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.primary}
                    onChange={(e) => handleColorChange('primary', e.target.value)}
                  />
                </div>
              </div>

              <div className="color-input">
                <label>Couleur secondaire</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.secondary}
                    onChange={(e) => handleColorChange('secondary', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.secondary}
                    onChange={(e) => handleColorChange('secondary', e.target.value)}
                  />
                </div>
              </div>

              <div className="color-input">
                <label>Couleur d'accent</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.accent}
                    onChange={(e) => handleColorChange('accent', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.accent}
                    onChange={(e) => handleColorChange('accent', e.target.value)}
                  />
                </div>
              </div>

              <div className="color-input">
                <label>Arrière-plan</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.background}
                    onChange={(e) => handleColorChange('background', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.background}
                    onChange={(e) => handleColorChange('background', e.target.value)}
                  />
                </div>
              </div>

              <div className="color-input">
                <label>Texte</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.text}
                    onChange={(e) => handleColorChange('text', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.text}
                    onChange={(e) => handleColorChange('text', e.target.value)}
                  />
                </div>
              </div>

              <div className="color-input">
                <label>Bordures</label>
                <div className="input-with-color">
                  <input
                    type="color"
                    value={design.colors.border}
                    onChange={(e) => handleColorChange('border', e.target.value)}
                  />
                  <input
                    type="text"
                    value={design.colors.border}
                    onChange={(e) => handleColorChange('border', e.target.value)}
                  />
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Onglet Typographie */}
        {activeTab === 'fonts' && (
          <div className="tab-content">
            <div className="form-group">
              <label>Police des titres</label>
              <select
                value={design.fonts.heading}
                onChange={(e) => handleFontChange('heading', e.target.value)}
              >
                {fontOptions.map(font => (
                  <option key={font.name} value={font.value}>{font.name}</option>
                ))}
              </select>
              <div className="font-preview" style={{ fontFamily: design.fonts.heading }}>
                Aperçu du titre
              </div>
            </div>

            <div className="form-group">
              <label>Police du corps de texte</label>
              <select
                value={design.fonts.body}
                onChange={(e) => handleFontChange('body', e.target.value)}
              >
                {fontOptions.map(font => (
                  <option key={font.name} value={font.value}>{font.name}</option>
                ))}
              </select>
              <div className="font-preview body-preview" style={{ fontFamily: design.fonts.body }}>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
              </div>
            </div>
          </div>
        )}

        {/* Onglet Espacements */}
        {activeTab === 'spacing' && (
          <div className="tab-content">
            <h4>Espacements</h4>
            <div className="spacing-inputs">
              <div className="spacing-input">
                <label>Petit (sm)</label>
                <input
                  type="text"
                  value={design.spacing.sm}
                  onChange={(e) => handleSpacingChange('sm', e.target.value)}
                />
              </div>
              <div className="spacing-input">
                <label>Moyen (md)</label>
                <input
                  type="text"
                  value={design.spacing.md}
                  onChange={(e) => handleSpacingChange('md', e.target.value)}
                />
              </div>
              <div className="spacing-input">
                <label>Grand (lg)</label>
                <input
                  type="text"
                  value={design.spacing.lg}
                  onChange={(e) => handleSpacingChange('lg', e.target.value)}
                />
              </div>
              <div className="spacing-input">
                <label>Extra large (xl)</label>
                <input
                  type="text"
                  value={design.spacing.xl}
                  onChange={(e) => handleSpacingChange('xl', e.target.value)}
                />
              </div>
            </div>

            <h4>Bordures arrondies</h4>
            <div className="spacing-inputs">
              <div className="spacing-input">
                <label>Petit</label>
                <input
                  type="text"
                  value={design.spacing.radiusSm}
                  onChange={(e) => handleSpacingChange('radiusSm', e.target.value)}
                />
                <div className="radius-preview" style={{ borderRadius: design.spacing.radiusSm }}></div>
              </div>
              <div className="spacing-input">
                <label>Moyen</label>
                <input
                  type="text"
                  value={design.spacing.radiusMd}
                  onChange={(e) => handleSpacingChange('radiusMd', e.target.value)}
                />
                <div className="radius-preview" style={{ borderRadius: design.spacing.radiusMd }}></div>
              </div>
              <div className="spacing-input">
                <label>Grand</label>
                <input
                  type="text"
                  value={design.spacing.radiusLg}
                  onChange={(e) => handleSpacingChange('radiusLg', e.target.value)}
                />
                <div className="radius-preview" style={{ borderRadius: design.spacing.radiusLg }}></div>
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="design-panel-footer">
        <button onClick={resetToDefaults} className="btn-reset">
          <RotateCcw size={16} />
          Réinitialiser
        </button>
        <button onClick={handleSave} className="btn-primary" disabled={saving}>
          <Save size={16} />
          {saving ? 'Enregistrement...' : 'Enregistrer'}
        </button>
      </div>

      <style>{`
        .design-panel {
          position: fixed;
          right: 0;
          top: 56px;
          bottom: 0;
          width: 380px;
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
        
        .design-panel-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .design-panel-header h3 {
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
        
        .design-tabs {
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
          transition: all 0.2s;
        }
        
        .tab:hover {
          color: #fff;
          background: rgba(255,255,255,0.05);
        }
        
        .tab.active {
          color: #3b82f6;
          border-bottom: 2px solid #3b82f6;
        }
        
        .design-panel-content {
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
        
        .presets-section label {
          display: block;
          color: #9ca3af;
          font-size: 12px;
          margin-bottom: 12px;
          text-transform: uppercase;
          letter-spacing: 1px;
        }
        
        .presets-grid {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 10px;
        }
        
        .preset-btn {
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 8px;
          padding: 12px;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .preset-btn:hover {
          border-color: #3b82f6;
          background: rgba(59,130,246,0.1);
        }
        
        .preset-colors {
          display: flex;
          gap: 4px;
          margin-bottom: 8px;
        }
        
        .preset-colors span {
          width: 20px;
          height: 20px;
          border-radius: 4px;
        }
        
        .preset-name {
          color: #e4e4e7;
          font-size: 11px;
        }
        
        hr {
          border: none;
          border-top: 1px solid #0f3460;
          margin: 20px 0;
        }
        
        .color-inputs {
          display: flex;
          flex-direction: column;
          gap: 16px;
        }
        
        .color-input label {
          display: block;
          color: #e4e4e7;
          font-size: 13px;
          margin-bottom: 8px;
        }
        
        .input-with-color {
          display: flex;
          gap: 8px;
        }
        
        .input-with-color input[type="color"] {
          width: 40px;
          height: 36px;
          border: none;
          border-radius: 6px;
          cursor: pointer;
        }
        
        .input-with-color input[type="text"] {
          flex: 1;
          padding: 8px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-family: monospace;
        }
        
        .form-group {
          margin-bottom: 24px;
        }
        
        .form-group label {
          display: block;
          color: #e4e4e7;
          font-size: 13px;
          margin-bottom: 8px;
        }
        
        .form-group select {
          width: 100%;
          padding: 10px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-size: 14px;
        }
        
        .font-preview {
          margin-top: 12px;
          padding: 16px;
          background: rgba(255,255,255,0.03);
          border-radius: 8px;
          color: #fff;
          font-size: 24px;
          font-weight: 600;
        }
        
        .body-preview {
          font-size: 14px;
          font-weight: 400;
          line-height: 1.6;
        }
        
        h4 {
          color: #e4e4e7;
          font-size: 14px;
          margin: 0 0 16px 0;
        }
        
        .spacing-inputs {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 12px;
          margin-bottom: 24px;
        }
        
        .spacing-input label {
          display: block;
          color: #9ca3af;
          font-size: 12px;
          margin-bottom: 6px;
        }
        
        .spacing-input input {
          width: 100%;
          padding: 8px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-family: monospace;
          font-size: 13px;
        }
        
        .radius-preview {
          width: 40px;
          height: 40px;
          background: #3b82f6;
          margin-top: 8px;
        }
        
        .design-panel-footer {
          display: flex;
          justify-content: space-between;
          padding: 16px;
          border-top: 1px solid #0f3460;
        }
        
        .btn-reset {
          display: flex;
          align-items: center;
          gap: 6px;
          padding: 10px 16px;
          background: rgba(255,255,255,0.1);
          border: none;
          border-radius: 6px;
          color: #9ca3af;
          font-size: 13px;
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

export default DesignSystemPanel;
