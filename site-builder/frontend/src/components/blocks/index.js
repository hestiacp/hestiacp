/**
 * ===========================================
 * Blocs personnalisés GrapesJS - Version améliorée
 * ===========================================
 */

export function registerCustomBlocks(editor) {
  const bm = editor.BlockManager;

  // ===========================================
  // CATÉGORIE: STRUCTURE
  // ===========================================
  
  bm.add('section-full', {
    label: 'Section pleine',
    category: 'Structure',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" fill="currentColor" opacity="0.3"/></svg>`,
    content: `<section style="min-height:300px;padding:60px 20px;background:#f8fafc;"></section>`
  });

  bm.add('container', {
    label: 'Conteneur',
    category: 'Structure',
    media: `<svg viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" fill="none" stroke="currentColor" stroke-width="2"/></svg>`,
    content: `<div style="max-width:1200px;margin:0 auto;padding:20px;"></div>`
  });

  bm.add('two-columns', {
    label: '2 Colonnes',
    category: 'Structure',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="9" height="16" fill="currentColor" opacity="0.5"/><rect x="13" y="4" width="9" height="16" fill="currentColor" opacity="0.5"/></svg>`,
    content: `<div style="display:flex;gap:30px;flex-wrap:wrap;"><div style="flex:1;min-width:280px;padding:20px;background:#fff;border-radius:8px;"></div><div style="flex:1;min-width:280px;padding:20px;background:#fff;border-radius:8px;"></div></div>`
  });

  bm.add('three-columns', {
    label: '3 Colonnes',
    category: 'Structure',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="6" height="16" fill="currentColor" opacity="0.5"/><rect x="9" y="4" width="6" height="16" fill="currentColor" opacity="0.5"/><rect x="16" y="4" width="6" height="16" fill="currentColor" opacity="0.5"/></svg>`,
    content: `<div style="display:flex;gap:20px;flex-wrap:wrap;"><div style="flex:1;min-width:200px;padding:20px;background:#fff;border-radius:8px;"></div><div style="flex:1;min-width:200px;padding:20px;background:#fff;border-radius:8px;"></div><div style="flex:1;min-width:200px;padding:20px;background:#fff;border-radius:8px;"></div></div>`
  });

  // ===========================================
  // CATÉGORIE: HERO
  // ===========================================

  bm.add('hero-split', {
    label: '🎯 Hero Image/Texte',
    category: 'Hero',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="10" height="16" fill="currentColor" opacity="0.3"/><rect x="13" y="4" width="9" height="16" fill="currentColor"/></svg>`,
    content: `
      <section style="display:flex;min-height:600px;flex-wrap:wrap;">
        <div style="flex:1;min-width:300px;padding:80px 60px;display:flex;flex-direction:column;justify-content:center;background:#fff;">
          <span style="color:#3b82f6;font-weight:600;font-size:14px;text-transform:uppercase;letter-spacing:2px;margin-bottom:20px;">Bienvenue</span>
          <h1 style="font-size:48px;font-weight:800;color:#1f2937;line-height:1.1;margin:0 0 24px 0;">Créez quelque chose d'extraordinaire</h1>
          <p style="font-size:18px;color:#6b7280;line-height:1.8;margin:0 0 40px 0;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore.</p>
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
            <a href="#" style="background:#3b82f6;color:#fff;padding:16px 32px;border-radius:8px;text-decoration:none;font-weight:600;">Commencer</a>
            <a href="#" style="background:#fff;color:#1f2937;padding:16px 32px;border-radius:8px;text-decoration:none;font-weight:600;border:2px solid #e5e7eb;">En savoir plus</a>
          </div>
        </div>
        <div style="flex:1;min-width:300px;min-height:400px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;">
          <span style="color:rgba(255,255,255,0.8);font-size:24px;">📷 Votre image ici</span>
        </div>
      </section>
    `
  });

  bm.add('hero-centered', {
    label: '🎯 Hero Centré',
    category: 'Hero',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" fill="currentColor" opacity="0.3"/><rect x="6" y="8" width="12" height="2" fill="currentColor"/><rect x="8" y="12" width="8" height="2" fill="currentColor"/></svg>`,
    content: `
      <section style="min-height:600px;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 20px;background:linear-gradient(135deg,#1f2937 0%,#111827 100%);">
        <div style="max-width:800px;">
          <h1 style="font-size:56px;font-weight:800;color:#fff;line-height:1.1;margin:0 0 24px 0;">Votre titre accrocheur</h1>
          <p style="font-size:20px;color:#9ca3af;line-height:1.8;margin:0 0 40px 0;">Une description captivante qui explique votre proposition de valeur unique.</p>
          <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="#" style="background:#3b82f6;color:#fff;padding:18px 40px;border-radius:8px;text-decoration:none;font-weight:600;">Démarrer</a>
            <a href="#" style="background:transparent;color:#fff;padding:18px 40px;border-radius:8px;text-decoration:none;font-weight:600;border:2px solid rgba(255,255,255,0.3);">Voir la démo</a>
          </div>
        </div>
      </section>
    `
  });

  bm.add('hero-video-bg', {
    label: '🎯 Hero avec Image',
    category: 'Hero',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" fill="currentColor" opacity="0.5"/><polygon points="10,8 16,12 10,16" fill="white"/></svg>`,
    content: `
      <section style="min-height:700px;display:flex;align-items:center;justify-content:center;text-align:center;padding:80px 20px;background:linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url('https://images.unsplash.com/photo-1497366216548-37526070297c?w=1920') center/cover;">
        <div style="max-width:900px;">
          <h1 style="font-size:64px;font-weight:800;color:#fff;line-height:1.1;margin:0 0 24px 0;text-shadow:0 2px 10px rgba(0,0,0,0.3);">Transformez votre vision</h1>
          <p style="font-size:22px;color:rgba(255,255,255,0.9);line-height:1.8;margin:0 0 50px 0;">Créez des expériences digitales exceptionnelles.</p>
          <a href="#" style="background:#fff;color:#1f2937;padding:20px 50px;border-radius:50px;text-decoration:none;font-weight:700;font-size:18px;">Découvrir</a>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: NAVIGATION
  // ===========================================

  bm.add('navbar-simple', {
    label: '📌 Navigation',
    category: 'Navigation',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="4" fill="currentColor"/></svg>`,
    content: `
      <nav style="display:flex;justify-content:space-between;align-items:center;padding:15px 30px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size:24px;font-weight:bold;color:#1f2937;">Logo</div>
        <div style="display:flex;gap:30px;">
          <a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">Accueil</a>
          <a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">Services</a>
          <a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">À propos</a>
          <a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;">Contact</a>
        </div>
        <a href="#" style="background:#3b82f6;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-weight:500;">Devis</a>
      </nav>
    `
  });

  // ===========================================
  // CATÉGORIE: SERVICES / FEATURES
  // ===========================================

  bm.add('features-grid', {
    label: '⭐ Grille Services',
    category: 'Services',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9" fill="currentColor" opacity="0.5"/><rect x="13" y="2" width="9" height="9" fill="currentColor" opacity="0.5"/><rect x="2" y="13" width="9" height="9" fill="currentColor" opacity="0.5"/><rect x="13" y="13" width="9" height="9" fill="currentColor" opacity="0.5"/></svg>`,
    content: `
      <section style="padding:80px 40px;background:#fff;">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Nos Services</h2>
          <p style="font-size:18px;color:#6b7280;margin:0;">Découvrez comment nous pouvons vous aider</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;max-width:1200px;margin:0 auto;">
          <div style="padding:40px;background:#f8fafc;border-radius:16px;text-align:center;">
            <div style="width:70px;height:70px;background:#dbeafe;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:32px;">🚀</div>
            <h3 style="font-size:22px;font-weight:600;color:#1f2937;margin:0 0 12px 0;">Performance</h3>
            <p style="font-size:16px;color:#6b7280;line-height:1.6;margin:0;">Sites ultra-rapides optimisés.</p>
          </div>
          <div style="padding:40px;background:#f8fafc;border-radius:16px;text-align:center;">
            <div style="width:70px;height:70px;background:#dcfce7;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:32px;">🎨</div>
            <h3 style="font-size:22px;font-weight:600;color:#1f2937;margin:0 0 12px 0;">Design</h3>
            <p style="font-size:16px;color:#6b7280;line-height:1.6;margin:0;">Interfaces modernes et intuitives.</p>
          </div>
          <div style="padding:40px;background:#f8fafc;border-radius:16px;text-align:center;">
            <div style="width:70px;height:70px;background:#fef3c7;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:32px;">📱</div>
            <h3 style="font-size:22px;font-weight:600;color:#1f2937;margin:0 0 12px 0;">Responsive</h3>
            <p style="font-size:16px;color:#6b7280;line-height:1.6;margin:0;">Adapté à tous les écrans.</p>
          </div>
          <div style="padding:40px;background:#f8fafc;border-radius:16px;text-align:center;">
            <div style="width:70px;height:70px;background:#fce7f3;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:32px;">🔒</div>
            <h3 style="font-size:22px;font-weight:600;color:#1f2937;margin:0 0 12px 0;">Sécurité</h3>
            <p style="font-size:16px;color:#6b7280;line-height:1.6;margin:0;">Protection optimale des données.</p>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: CONTENU
  // ===========================================

  bm.add('text-image-left', {
    label: '📝 Image + Texte',
    category: 'Contenu',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="8" height="16" fill="currentColor"/><rect x="12" y="6" width="10" height="2" fill="currentColor" opacity="0.5"/><rect x="12" y="10" width="10" height="2" fill="currentColor" opacity="0.5"/></svg>`,
    content: `
      <section style="display:flex;gap:60px;align-items:center;padding:80px 40px;flex-wrap:wrap;background:#fff;">
        <div style="flex:1;min-width:300px;">
          <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=600" style="width:100%;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,0.1);" alt="Image">
        </div>
        <div style="flex:1;min-width:300px;">
          <span style="color:#3b82f6;font-weight:600;font-size:14px;text-transform:uppercase;letter-spacing:2px;">Notre histoire</span>
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:16px 0 24px 0;">Une équipe passionnée</h2>
          <p style="font-size:18px;color:#6b7280;line-height:1.8;margin:0 0 30px 0;">Depuis plus de 10 ans, nous accompagnons les entreprises dans leur transformation digitale.</p>
          <a href="#" style="color:#3b82f6;font-weight:600;text-decoration:none;">En savoir plus →</a>
        </div>
      </section>
    `
  });

  bm.add('text-image-right', {
    label: '📝 Texte + Image',
    category: 'Contenu',
    media: `<svg viewBox="0 0 24 24"><rect x="14" y="4" width="8" height="16" fill="currentColor"/><rect x="2" y="6" width="10" height="2" fill="currentColor" opacity="0.5"/><rect x="2" y="10" width="10" height="2" fill="currentColor" opacity="0.5"/></svg>`,
    content: `
      <section style="display:flex;gap:60px;align-items:center;padding:80px 40px;flex-wrap:wrap-reverse;background:#f8fafc;">
        <div style="flex:1;min-width:300px;">
          <span style="color:#10b981;font-weight:600;font-size:14px;text-transform:uppercase;letter-spacing:2px;">Nos valeurs</span>
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:16px 0 24px 0;">L'excellence au quotidien</h2>
          <p style="font-size:18px;color:#6b7280;line-height:1.8;margin:0 0 30px 0;">Nous croyons que chaque projet mérite une attention particulière et des résultats exceptionnels.</p>
          <ul style="list-style:none;padding:0;margin:0;">
            <li style="display:flex;align-items:center;gap:12px;margin-bottom:12px;color:#4b5563;"><span style="color:#10b981;">✓</span> Qualité irréprochable</li>
            <li style="display:flex;align-items:center;gap:12px;margin-bottom:12px;color:#4b5563;"><span style="color:#10b981;">✓</span> Délais respectés</li>
            <li style="display:flex;align-items:center;gap:12px;color:#4b5563;"><span style="color:#10b981;">✓</span> Support réactif</li>
          </ul>
        </div>
        <div style="flex:1;min-width:300px;">
          <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600" style="width:100%;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,0.1);" alt="Image">
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: TÉMOIGNAGES
  // ===========================================

  bm.add('testimonial-single', {
    label: '💬 Témoignage',
    category: 'Témoignages',
    media: `<svg viewBox="0 0 24 24"><text x="4" y="18" font-size="20" fill="currentColor">"</text></svg>`,
    content: `
      <section style="padding:80px 40px;background:#f8fafc;">
        <div style="max-width:800px;margin:0 auto;text-align:center;">
          <div style="font-size:60px;color:#3b82f6;line-height:1;margin-bottom:20px;">"</div>
          <p style="font-size:24px;color:#1f2937;line-height:1.8;margin:0 0 40px 0;font-style:italic;">Ce service a complètement transformé notre présence en ligne. Les résultats ont dépassé toutes nos attentes.</p>
          <div style="display:flex;align-items:center;justify-content:center;gap:16px;">
            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100" style="width:60px;height:60px;border-radius:50%;object-fit:cover;" alt="Avatar">
            <div style="text-align:left;">
              <div style="font-weight:600;color:#1f2937;font-size:18px;">Marie Dupont</div>
              <div style="color:#6b7280;font-size:14px;">CEO, Startup XYZ</div>
            </div>
          </div>
        </div>
      </section>
    `
  });

  bm.add('testimonials-grid', {
    label: '💬 Grille Témoignages',
    category: 'Témoignages',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="9" height="9" fill="currentColor" opacity="0.5"/><rect x="13" y="2" width="9" height="9" fill="currentColor" opacity="0.5"/><rect x="2" y="13" width="9" height="9" fill="currentColor" opacity="0.5"/></svg>`,
    content: `
      <section style="padding:80px 40px;background:#fff;">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Témoignages</h2>
          <p style="font-size:18px;color:#6b7280;margin:0;">Ce que disent nos clients</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:30px;max-width:1200px;margin:0 auto;">
          <div style="padding:40px;background:#f8fafc;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.05);">
            <div style="color:#fbbf24;font-size:20px;margin-bottom:20px;">★★★★★</div>
            <p style="font-size:16px;color:#4b5563;line-height:1.8;margin:0 0 24px 0;">"Une équipe professionnelle et à l'écoute. Le résultat est exactement ce que nous voulions."</p>
            <div style="display:flex;align-items:center;gap:12px;">
              <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100" style="width:48px;height:48px;border-radius:50%;object-fit:cover;" alt="Avatar">
              <div>
                <div style="font-weight:600;color:#1f2937;">Jean Martin</div>
                <div style="color:#6b7280;font-size:14px;">CEO, ABC Corp</div>
              </div>
            </div>
          </div>
          <div style="padding:40px;background:#f8fafc;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.05);">
            <div style="color:#fbbf24;font-size:20px;margin-bottom:20px;">★★★★★</div>
            <p style="font-size:16px;color:#4b5563;line-height:1.8;margin:0 0 24px 0;">"Rapport qualité-prix excellent. Je recommande vivement leurs services."</p>
            <div style="display:flex;align-items:center;gap:12px;">
              <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100" style="width:48px;height:48px;border-radius:50%;object-fit:cover;" alt="Avatar">
              <div>
                <div style="font-weight:600;color:#1f2937;">Sophie Bernard</div>
                <div style="color:#6b7280;font-size:14px;">Fondatrice, Studio</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: GALERIE
  // ===========================================

  bm.add('gallery-grid', {
    label: '🖼️ Galerie Photos',
    category: 'Galerie',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="6" height="6" fill="currentColor"/><rect x="9" y="2" width="6" height="6" fill="currentColor"/><rect x="16" y="2" width="6" height="6" fill="currentColor"/><rect x="2" y="9" width="6" height="6" fill="currentColor"/></svg>`,
    content: `
      <section style="padding:80px 40px;background:#fff;">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Portfolio</h2>
          <p style="font-size:18px;color:#6b7280;margin:0;">Nos dernières réalisations</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;max-width:1200px;margin:0 auto;">
          <div style="overflow:hidden;border-radius:12px;aspect-ratio:4/3;">
            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600" style="width:100%;height:100%;object-fit:cover;" alt="Projet 1">
          </div>
          <div style="overflow:hidden;border-radius:12px;aspect-ratio:4/3;">
            <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600" style="width:100%;height:100%;object-fit:cover;" alt="Projet 2">
          </div>
          <div style="overflow:hidden;border-radius:12px;aspect-ratio:4/3;">
            <img src="https://images.unsplash.com/photo-1504639725590-34d0984388bd?w=600" style="width:100%;height:100%;object-fit:cover;" alt="Projet 3">
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: PRICING
  // ===========================================

  bm.add('pricing-table', {
    label: '💰 Tableau de Prix',
    category: 'Pricing',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="2" width="6" height="20" fill="currentColor" opacity="0.3"/><rect x="9" y="4" width="6" height="18" fill="currentColor"/><rect x="16" y="2" width="6" height="20" fill="currentColor" opacity="0.3"/></svg>`,
    content: `
      <section style="padding:80px 40px;background:#f8fafc;">
        <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Nos Tarifs</h2>
          <p style="font-size:18px;color:#6b7280;margin:0;">Choisissez l'offre adaptée</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;max-width:900px;margin:0 auto;align-items:start;">
          <div style="padding:40px;background:#fff;border-radius:16px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.05);">
            <h3 style="font-size:24px;font-weight:600;color:#1f2937;margin:0 0 8px 0;">Starter</h3>
            <div style="font-size:48px;font-weight:800;color:#1f2937;margin:20px 0;">29€<span style="font-size:16px;color:#6b7280;">/mois</span></div>
            <ul style="list-style:none;padding:0;margin:0 0 30px 0;text-align:left;">
              <li style="padding:12px 0;border-bottom:1px solid #f1f5f9;color:#4b5563;">✓ 5 pages</li>
              <li style="padding:12px 0;border-bottom:1px solid #f1f5f9;color:#4b5563;">✓ Support email</li>
              <li style="padding:12px 0;color:#4b5563;">✓ SSL inclus</li>
            </ul>
            <a href="#" style="display:block;padding:16px;background:#f1f5f9;color:#1f2937;border-radius:8px;text-decoration:none;font-weight:600;">Choisir</a>
          </div>
          <div style="padding:40px;background:#3b82f6;border-radius:16px;text-align:center;transform:scale(1.05);box-shadow:0 20px 40px rgba(59,130,246,0.3);">
            <div style="background:#1d4ed8;color:#fff;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;display:inline-block;margin-bottom:16px;">POPULAIRE</div>
            <h3 style="font-size:24px;font-weight:600;color:#fff;margin:0 0 8px 0;">Pro</h3>
            <div style="font-size:48px;font-weight:800;color:#fff;margin:20px 0;">79€<span style="font-size:16px;color:rgba(255,255,255,0.8);">/mois</span></div>
            <ul style="list-style:none;padding:0;margin:0 0 30px 0;text-align:left;">
              <li style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.2);color:#fff;">✓ Pages illimitées</li>
              <li style="padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.2);color:#fff;">✓ Support prioritaire</li>
              <li style="padding:12px 0;color:#fff;">✓ SSL + CDN</li>
            </ul>
            <a href="#" style="display:block;padding:16px;background:#fff;color:#3b82f6;border-radius:8px;text-decoration:none;font-weight:600;">Choisir</a>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: CONTACT
  // ===========================================

  bm.add('contact-form', {
    label: '📧 Formulaire Contact',
    category: 'Contact',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 4l10 8 10-8" fill="none" stroke="currentColor" stroke-width="2"/></svg>`,
    content: `
      <section style="display:flex;gap:60px;padding:80px 40px;flex-wrap:wrap;background:#fff;">
        <div style="flex:1;min-width:300px;">
          <span style="color:#3b82f6;font-weight:600;font-size:14px;text-transform:uppercase;letter-spacing:2px;">Contact</span>
          <h2 style="font-size:40px;font-weight:700;color:#1f2937;margin:16px 0 24px 0;">Parlons de votre projet</h2>
          <p style="font-size:18px;color:#6b7280;line-height:1.8;margin:0 0 40px 0;">Nous sommes là pour répondre à vos questions.</p>
          <div style="display:flex;flex-direction:column;gap:20px;">
            <div style="display:flex;align-items:center;gap:16px;">
              <div style="width:50px;height:50px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;">📍</div>
              <div><div style="font-weight:600;color:#1f2937;">Adresse</div><div style="color:#6b7280;">123 Rue Example, Paris</div></div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
              <div style="width:50px;height:50px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;">📧</div>
              <div><div style="font-weight:600;color:#1f2937;">Email</div><div style="color:#6b7280;">contact@example.com</div></div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
              <div style="width:50px;height:50px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px;">📞</div>
              <div><div style="font-weight:600;color:#1f2937;">Téléphone</div><div style="color:#6b7280;">+33 1 23 45 67 89</div></div>
            </div>
          </div>
        </div>
        <div style="flex:1;min-width:300px;background:#f8fafc;padding:40px;border-radius:16px;">
          <form style="display:flex;flex-direction:column;gap:20px;">
            <div>
              <label style="display:block;font-weight:500;color:#374151;margin-bottom:8px;">Nom</label>
              <input type="text" placeholder="Votre nom" style="width:100%;padding:14px 16px;border:1px solid #e5e7eb;border-radius:8px;font-size:16px;box-sizing:border-box;">
            </div>
            <div>
              <label style="display:block;font-weight:500;color:#374151;margin-bottom:8px;">Email</label>
              <input type="email" placeholder="votre@email.com" style="width:100%;padding:14px 16px;border:1px solid #e5e7eb;border-radius:8px;font-size:16px;box-sizing:border-box;">
            </div>
            <div>
              <label style="display:block;font-weight:500;color:#374151;margin-bottom:8px;">Message</label>
              <textarea placeholder="Votre message..." rows="4" style="width:100%;padding:14px 16px;border:1px solid #e5e7eb;border-radius:8px;font-size:16px;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <button type="submit" style="background:#3b82f6;color:#fff;padding:16px;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;">Envoyer</button>
          </form>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: CTA
  // ===========================================

  bm.add('cta-simple', {
    label: '🔔 Call to Action',
    category: 'CTA',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="8" width="20" height="8" fill="currentColor"/></svg>`,
    content: `
      <section style="padding:80px 40px;background:linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%);text-align:center;">
        <div style="max-width:700px;margin:0 auto;">
          <h2 style="font-size:40px;font-weight:700;color:#fff;margin:0 0 16px 0;">Prêt à commencer ?</h2>
          <p style="font-size:20px;color:rgba(255,255,255,0.9);margin:0 0 40px 0;">Rejoignez des milliers de clients satisfaits.</p>
          <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="#" style="background:#fff;color:#3b82f6;padding:18px 40px;border-radius:8px;text-decoration:none;font-weight:600;">Démarrer</a>
            <a href="#" style="background:transparent;color:#fff;padding:18px 40px;border-radius:8px;text-decoration:none;font-weight:600;border:2px solid rgba(255,255,255,0.5);">En savoir plus</a>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: FOOTER
  // ===========================================

  bm.add('footer-simple', {
    label: '📌 Footer Simple',
    category: 'Footer',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="18" width="20" height="4" fill="currentColor"/></svg>`,
    content: `<footer style="padding:40px;background:#1f2937;text-align:center;"><p style="color:#9ca3af;margin:0;">© 2024 Votre Entreprise. Tous droits réservés.</p></footer>`
  });

  bm.add('footer-full', {
    label: '📌 Footer Complet',
    category: 'Footer',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="14" width="20" height="8" fill="currentColor"/></svg>`,
    content: `
      <footer style="padding:60px 40px 30px;background:#111827;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:40px;max-width:1200px;margin:0 auto 40px;">
          <div>
            <div style="font-size:24px;font-weight:bold;color:#fff;margin-bottom:20px;">Logo</div>
            <p style="color:#9ca3af;line-height:1.8;margin:0;">Créez des expériences digitales exceptionnelles.</p>
          </div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:16px;">Navigation</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <a href="#" style="color:#9ca3af;text-decoration:none;">Accueil</a>
              <a href="#" style="color:#9ca3af;text-decoration:none;">Services</a>
              <a href="#" style="color:#9ca3af;text-decoration:none;">Contact</a>
            </div>
          </div>
          <div>
            <div style="font-weight:600;color:#fff;margin-bottom:16px;">Contact</div>
            <div style="color:#9ca3af;line-height:1.8;">123 Rue Example<br>Paris, France<br>contact@example.com</div>
          </div>
        </div>
        <div style="border-top:1px solid #374151;padding-top:30px;text-align:center;color:#6b7280;font-size:14px;">© 2024 Votre Entreprise</div>
      </footer>
    `
  });

  // ===========================================
  // CATÉGORIE: ÉLÉMENTS BASIQUES
  // ===========================================

  bm.add('heading', {
    label: 'Titre H1',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><text x="6" y="17" font-size="14" font-weight="bold" fill="currentColor">H1</text></svg>`,
    content: `<h1 style="font-size:48px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Votre titre ici</h1>`
  });

  bm.add('heading-h2', {
    label: 'Titre H2',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><text x="6" y="17" font-size="14" font-weight="bold" fill="currentColor">H2</text></svg>`,
    content: `<h2 style="font-size:36px;font-weight:700;color:#1f2937;margin:0 0 16px 0;">Sous-titre ici</h2>`
  });

  bm.add('paragraph', {
    label: 'Paragraphe',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="2" fill="currentColor"/><rect x="2" y="9" width="20" height="2" fill="currentColor"/><rect x="2" y="13" width="14" height="2" fill="currentColor"/></svg>`,
    content: `<p style="font-size:18px;color:#6b7280;line-height:1.8;margin:0 0 16px 0;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>`
  });

  bm.add('image', {
    label: 'Image',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="8" cy="10" r="2" fill="currentColor"/><path d="M2 18l6-6 4 4 6-6 4 4" stroke="currentColor" stroke-width="2" fill="none"/></svg>`,
    content: `<img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800" style="width:100%;max-width:800px;border-radius:12px;" alt="Image">`
  });

  bm.add('button-primary', {
    label: 'Bouton',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="4" y="8" width="16" height="8" rx="4" fill="currentColor"/></svg>`,
    content: `<a href="#" style="display:inline-block;background:#3b82f6;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;">Bouton</a>`
  });

  bm.add('button-outline', {
    label: 'Bouton Outline',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="4" y="8" width="16" height="8" rx="4" fill="none" stroke="currentColor" stroke-width="2"/></svg>`,
    content: `<a href="#" style="display:inline-block;background:transparent;color:#3b82f6;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;border:2px solid #3b82f6;">Bouton</a>`
  });

  bm.add('divider', {
    label: 'Séparateur',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><line x1="2" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2"/></svg>`,
    content: `<hr style="border:none;height:1px;background:#e5e7eb;margin:40px 0;">`
  });

  bm.add('spacer', {
    label: 'Espace',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="10" y="2" width="4" height="20" fill="currentColor" opacity="0.3"/></svg>`,
    content: `<div style="height:60px;"></div>`
  });

  bm.add('video', {
    label: 'Vidéo YouTube',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" fill="currentColor" opacity="0.3"/><polygon points="10,8 16,12 10,16" fill="currentColor"/></svg>`,
    content: `<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;border-radius:12px;"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen></iframe></div>`
  });

  bm.add('map', {
    label: 'Carte Google',
    category: 'Basique',
    media: `<svg viewBox="0 0 24 24"><circle cx="12" cy="10" r="3" fill="currentColor"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" fill="none" stroke="currentColor" stroke-width="2"/></svg>`,
    content: `<div style="border-radius:12px;overflow:hidden;"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.9916256937604!2d2.292292615509614!3d48.85837007928746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e2964e34e2d%3A0x8ddca9ee380ef7e0!2sTour%20Eiffel!5e0!3m2!1sfr!2sfr!4v1" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe></div>`
  });

  console.log('✅ Blocs personnalisés chargés avec succès !');
}
