/**
 * ===========================================
 * Blocs avancés : Accordéon, Tabs, Icons, Navigation
 * ===========================================
 * 
 * Composants interactifs avec JavaScript intégré.
 */

export function registerAdvancedBlocks(editor) {
  const bm = editor.BlockManager;

  // ===========================================
  // CATÉGORIE: NAVIGATION
  // ===========================================

  // Menu Navigation Dynamique
  bm.add('navbar-full', {
    label: '🔗 Navigation complète',
    category: 'Navigation',
    content: `
      <nav class="navbar" style="display:flex;justify-content:space-between;align-items:center;padding:20px 40px;background:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.1);position:sticky;top:0;z-index:1000;">
        <div class="navbar-brand" style="font-size:24px;font-weight:700;color:#1f2937;">Logo</div>
        <ul class="navbar-menu" style="display:flex;gap:30px;list-style:none;margin:0;padding:0;">
          <li><a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;transition:color 0.3s;">Accueil</a></li>
          <li><a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;transition:color 0.3s;">Services</a></li>
          <li><a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;transition:color 0.3s;">À propos</a></li>
          <li><a href="#" style="color:#4b5563;text-decoration:none;font-weight:500;transition:color 0.3s;">Contact</a></li>
        </ul>
        <a href="#" class="navbar-cta" style="background:#3b82f6;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;">Action</a>
        <button class="navbar-toggle" style="display:none;background:none;border:none;font-size:24px;cursor:pointer;">☰</button>
      </nav>
      <style>
        @media (max-width: 768px) {
          .navbar { flex-wrap: wrap; }
          .navbar-menu { display: none !important; width: 100%; flex-direction: column; gap: 15px; padding: 20px 0; }
          .navbar-menu.active { display: flex !important; }
          .navbar-toggle { display: block !important; }
          .navbar-cta { display: none; }
        }
      </style>
      <script>
        document.querySelector('.navbar-toggle')?.addEventListener('click', function() {
          document.querySelector('.navbar-menu')?.classList.toggle('active');
        });
      </script>
    `
  });

  // Menu avec dropdown
  bm.add('navbar-dropdown', {
    label: '🔽 Menu avec dropdown',
    category: 'Navigation',
    content: `
      <nav style="display:flex;justify-content:space-between;align-items:center;padding:20px 40px;background:#1f2937;">
        <div style="font-size:24px;font-weight:700;color:#fff;">Logo</div>
        <div style="display:flex;gap:30px;align-items:center;">
          <a href="#" style="color:#fff;text-decoration:none;">Accueil</a>
          <div class="dropdown" style="position:relative;">
            <a href="#" style="color:#fff;text-decoration:none;display:flex;align-items:center;gap:6px;">
              Services <span style="font-size:12px;">▼</span>
            </a>
            <div class="dropdown-menu" style="position:absolute;top:100%;left:0;background:#fff;min-width:200px;box-shadow:0 10px 40px rgba(0,0,0,0.2);border-radius:8px;padding:10px 0;display:none;margin-top:10px;">
              <a href="#" style="display:block;padding:12px 20px;color:#1f2937;text-decoration:none;">Consulting</a>
              <a href="#" style="display:block;padding:12px 20px;color:#1f2937;text-decoration:none;">Formation</a>
              <a href="#" style="display:block;padding:12px 20px;color:#1f2937;text-decoration:none;">Support</a>
            </div>
          </div>
          <a href="#" style="color:#fff;text-decoration:none;">Contact</a>
        </div>
      </nav>
      <style>
        .dropdown:hover .dropdown-menu { display: block !important; }
        .dropdown-menu a:hover { background: #f3f4f6; }
      </style>
    `
  });

  // ===========================================
  // CATÉGORIE: ACCORDÉONS
  // ===========================================

  // Accordéon simple
  bm.add('accordion', {
    label: '📂 Accordéon',
    category: 'Interactif',
    content: `
      <div class="accordion" style="max-width:800px;margin:0 auto;">
        <div class="accordion-item" style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;overflow:hidden;">
          <button class="accordion-header" style="width:100%;padding:20px;background:#f9fafb;border:none;text-align:left;font-size:16px;font-weight:600;color:#1f2937;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
            Comment fonctionne votre service ?
            <span class="accordion-icon" style="font-size:20px;transition:transform 0.3s;">+</span>
          </button>
          <div class="accordion-content" style="padding:0 20px;max-height:0;overflow:hidden;transition:all 0.3s;background:#fff;">
            <p style="padding:20px 0;color:#4b5563;line-height:1.7;margin:0;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
          </div>
        </div>
        <div class="accordion-item" style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;overflow:hidden;">
          <button class="accordion-header" style="width:100%;padding:20px;background:#f9fafb;border:none;text-align:left;font-size:16px;font-weight:600;color:#1f2937;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
            Quels sont les tarifs ?
            <span class="accordion-icon" style="font-size:20px;transition:transform 0.3s;">+</span>
          </button>
          <div class="accordion-content" style="padding:0 20px;max-height:0;overflow:hidden;transition:all 0.3s;background:#fff;">
            <p style="padding:20px 0;color:#4b5563;line-height:1.7;margin:0;">Nos tarifs varient selon vos besoins. Contactez-nous pour un devis personnalisé gratuit.</p>
          </div>
        </div>
        <div class="accordion-item" style="border:1px solid #e5e7eb;border-radius:8px;margin-bottom:10px;overflow:hidden;">
          <button class="accordion-header" style="width:100%;padding:20px;background:#f9fafb;border:none;text-align:left;font-size:16px;font-weight:600;color:#1f2937;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
            Comment vous contacter ?
            <span class="accordion-icon" style="font-size:20px;transition:transform 0.3s;">+</span>
          </button>
          <div class="accordion-content" style="padding:0 20px;max-height:0;overflow:hidden;transition:all 0.3s;background:#fff;">
            <p style="padding:20px 0;color:#4b5563;line-height:1.7;margin:0;">Vous pouvez nous contacter par email, téléphone ou via le formulaire de contact sur notre site.</p>
          </div>
        </div>
      </div>
      <script>
        document.querySelectorAll('.accordion-header').forEach(header => {
          header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-icon');
            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';
            
            document.querySelectorAll('.accordion-content').forEach(c => c.style.maxHeight = '0px');
            document.querySelectorAll('.accordion-icon').forEach(i => { i.textContent = '+'; i.style.transform = 'rotate(0deg)'; });
            
            if (!isOpen) {
              content.style.maxHeight = content.scrollHeight + 'px';
              icon.textContent = '−';
              icon.style.transform = 'rotate(180deg)';
            }
          });
        });
      </script>
    `
  });

  // FAQ stylisée
  bm.add('faq', {
    label: '❓ FAQ',
    category: 'Interactif',
    content: `
      <section style="padding:80px 40px;background:#f9fafb;">
        <div style="text-align:center;margin-bottom:60px;">
          <h2 style="font-size:40px;font-weight:800;color:#1f2937;margin:0 0 16px 0;">Questions fréquentes</h2>
          <p style="font-size:18px;color:#6b7280;max-width:600px;margin:0 auto;">Trouvez rapidement les réponses à vos questions</p>
        </div>
        <div class="faq-list" style="max-width:800px;margin:0 auto;">
          <div class="faq-item" style="background:#fff;border-radius:12px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <button class="faq-question" style="width:100%;padding:24px;background:none;border:none;text-align:left;font-size:18px;font-weight:600;color:#1f2937;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
              Quelle est votre politique de remboursement ?
              <span style="width:24px;height:24px;background:#3b82f6;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;">+</span>
            </button>
            <div class="faq-answer" style="padding:0 24px;max-height:0;overflow:hidden;transition:all 0.3s;">
              <p style="padding-bottom:24px;color:#6b7280;line-height:1.8;margin:0;">Nous offrons un remboursement complet sous 30 jours si vous n'êtes pas satisfait de nos services.</p>
            </div>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: TABS
  // ===========================================

  // Tabs horizontaux
  bm.add('tabs', {
    label: '📑 Onglets',
    category: 'Interactif',
    content: `
      <div class="tabs-container" style="max-width:800px;margin:0 auto;padding:40px;">
        <div class="tabs-nav" style="display:flex;border-bottom:2px solid #e5e7eb;margin-bottom:30px;">
          <button class="tab-btn active" data-tab="tab1" style="padding:16px 32px;background:none;border:none;border-bottom:2px solid #3b82f6;margin-bottom:-2px;color:#3b82f6;font-weight:600;cursor:pointer;transition:all 0.3s;">Description</button>
          <button class="tab-btn" data-tab="tab2" style="padding:16px 32px;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;font-weight:600;cursor:pointer;transition:all 0.3s;">Caractéristiques</button>
          <button class="tab-btn" data-tab="tab3" style="padding:16px 32px;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;color:#6b7280;font-weight:600;cursor:pointer;transition:all 0.3s;">Avis</button>
        </div>
        <div class="tabs-content">
          <div class="tab-panel active" id="tab1" style="animation:fadeIn 0.3s;">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">Description du produit</h3>
            <p style="color:#6b7280;line-height:1.8;margin:0;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco.</p>
          </div>
          <div class="tab-panel" id="tab2" style="display:none;animation:fadeIn 0.3s;">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">Caractéristiques techniques</h3>
            <ul style="color:#6b7280;line-height:2;padding-left:20px;margin:0;">
              <li>Caractéristique 1</li>
              <li>Caractéristique 2</li>
              <li>Caractéristique 3</li>
            </ul>
          </div>
          <div class="tab-panel" id="tab3" style="display:none;animation:fadeIn 0.3s;">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">Avis clients</h3>
            <p style="color:#6b7280;line-height:1.8;margin:0;">⭐⭐⭐⭐⭐ - "Excellent produit, je recommande !"</p>
          </div>
        </div>
      </div>
      <style>
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
      </style>
      <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => {
              b.classList.remove('active');
              b.style.borderBottomColor = 'transparent';
              b.style.color = '#6b7280';
            });
            document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
            
            this.classList.add('active');
            this.style.borderBottomColor = '#3b82f6';
            this.style.color = '#3b82f6';
            document.getElementById(this.dataset.tab).style.display = 'block';
          });
        });
      </script>
    `
  });

  // Tabs verticaux
  bm.add('tabs-vertical', {
    label: '📊 Onglets verticaux',
    category: 'Interactif',
    content: `
      <div class="vtabs" style="display:flex;gap:40px;max-width:900px;margin:0 auto;padding:40px;">
        <div class="vtabs-nav" style="min-width:200px;">
          <button class="vtab-btn active" data-vtab="vtab1" style="width:100%;padding:16px 20px;background:#3b82f6;border:none;text-align:left;color:#fff;font-weight:600;cursor:pointer;border-radius:8px;margin-bottom:8px;">Fonctionnalités</button>
          <button class="vtab-btn" data-vtab="vtab2" style="width:100%;padding:16px 20px;background:#f3f4f6;border:none;text-align:left;color:#4b5563;font-weight:600;cursor:pointer;border-radius:8px;margin-bottom:8px;">Intégrations</button>
          <button class="vtab-btn" data-vtab="vtab3" style="width:100%;padding:16px 20px;background:#f3f4f6;border:none;text-align:left;color:#4b5563;font-weight:600;cursor:pointer;border-radius:8px;">Support</button>
        </div>
        <div class="vtabs-content" style="flex:1;">
          <div class="vtab-panel" id="vtab1">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">Fonctionnalités avancées</h3>
            <p style="color:#6b7280;line-height:1.8;">Découvrez toutes nos fonctionnalités pour booster votre productivité.</p>
          </div>
          <div class="vtab-panel" id="vtab2" style="display:none;">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">+200 Intégrations</h3>
            <p style="color:#6b7280;line-height:1.8;">Connectez vos outils favoris en quelques clics.</p>
          </div>
          <div class="vtab-panel" id="vtab3" style="display:none;">
            <h3 style="color:#1f2937;margin:0 0 16px 0;">Support 24/7</h3>
            <p style="color:#6b7280;line-height:1.8;">Notre équipe est disponible pour vous aider.</p>
          </div>
        </div>
      </div>
      <script>
        document.querySelectorAll('.vtab-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            document.querySelectorAll('.vtab-btn').forEach(b => {
              b.style.background = '#f3f4f6';
              b.style.color = '#4b5563';
            });
            document.querySelectorAll('.vtab-panel').forEach(p => p.style.display = 'none');
            
            this.style.background = '#3b82f6';
            this.style.color = '#fff';
            document.getElementById(this.dataset.vtab).style.display = 'block';
          });
        });
      </script>
    `
  });

  // ===========================================
  // CATÉGORIE: ICÔNES
  // ===========================================

  // Grille d'icônes avec texte
  bm.add('icon-grid', {
    label: '🎯 Grille icônes',
    category: 'Icônes',
    content: `
      <section style="padding:80px 40px;background:#fff;">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:40px;max-width:1000px;margin:0 auto;">
          <div style="text-align:center;">
            <div style="width:80px;height:80px;background:#eff6ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">🚀</div>
            <h4 style="color:#1f2937;margin:0 0 8px 0;">Rapide</h4>
            <p style="color:#6b7280;font-size:14px;margin:0;">Performance optimale</p>
          </div>
          <div style="text-align:center;">
            <div style="width:80px;height:80px;background:#f0fdf4;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">🔒</div>
            <h4 style="color:#1f2937;margin:0 0 8px 0;">Sécurisé</h4>
            <p style="color:#6b7280;font-size:14px;margin:0;">Protection maximale</p>
          </div>
          <div style="text-align:center;">
            <div style="width:80px;height:80px;background:#fef3c7;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">💡</div>
            <h4 style="color:#1f2937;margin:0 0 8px 0;">Intuitif</h4>
            <p style="color:#6b7280;font-size:14px;margin:0;">Facile à utiliser</p>
          </div>
          <div style="text-align:center;">
            <div style="width:80px;height:80px;background:#fce7f3;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:36px;">❤️</div>
            <h4 style="color:#1f2937;margin:0 0 8px 0;">Fiable</h4>
            <p style="color:#6b7280;font-size:14px;margin:0;">99.9% uptime</p>
          </div>
        </div>
      </section>
    `
  });

  // Icônes en ligne
  bm.add('icon-row', {
    label: '➡️ Icônes en ligne',
    category: 'Icônes',
    content: `
      <div style="display:flex;justify-content:center;gap:60px;padding:40px;background:#f9fafb;">
        <div style="display:flex;align-items:center;gap:16px;">
          <span style="font-size:32px;">📧</span>
          <div>
            <div style="color:#1f2937;font-weight:600;">Email</div>
            <div style="color:#6b7280;font-size:14px;">contact@example.com</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
          <span style="font-size:32px;">📞</span>
          <div>
            <div style="color:#1f2937;font-weight:600;">Téléphone</div>
            <div style="color:#6b7280;font-size:14px;">+33 1 23 45 67 89</div>
          </div>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
          <span style="font-size:32px;">📍</span>
          <div>
            <div style="color:#1f2937;font-weight:600;">Adresse</div>
            <div style="color:#6b7280;font-size:14px;">Paris, France</div>
          </div>
        </div>
      </div>
    `
  });

  // Réseaux sociaux
  bm.add('social-icons', {
    label: '📱 Réseaux sociaux',
    category: 'Icônes',
    content: `
      <div style="display:flex;justify-content:center;gap:20px;padding:30px;">
        <a href="#" style="width:50px;height:50px;background:#1877f2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:20px;" title="Facebook">f</a>
        <a href="#" style="width:50px;height:50px;background:#1da1f2;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:20px;" title="Twitter">𝕏</a>
        <a href="#" style="width:50px;height:50px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:20px;" title="Instagram">📷</a>
        <a href="#" style="width:50px;height:50px;background:#0077b5;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:20px;" title="LinkedIn">in</a>
        <a href="#" style="width:50px;height:50px;background:#ff0000;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:20px;" title="YouTube">▶</a>
      </div>
    `
  });

  // ===========================================
  // CATÉGORIE: MODALS / POPUPS
  // ===========================================

  // Modal simple
  bm.add('modal', {
    label: '🪟 Modal / Popup',
    category: 'Interactif',
    content: `
      <button class="open-modal-btn" style="background:#3b82f6;color:#fff;padding:14px 28px;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;">Ouvrir la popup</button>
      
      <div class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div class="modal-content" style="background:#fff;padding:40px;border-radius:16px;max-width:500px;width:90%;position:relative;animation:modalIn 0.3s;">
          <button class="close-modal-btn" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#9ca3af;">×</button>
          <h3 style="margin:0 0 16px 0;color:#1f2937;font-size:24px;">Titre de la popup</h3>
          <p style="color:#6b7280;line-height:1.7;margin:0 0 24px 0;">Contenu de votre popup. Vous pouvez y mettre un formulaire, une offre spéciale, ou toute autre information importante.</p>
          <button class="close-modal-btn" style="background:#3b82f6;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Fermer</button>
        </div>
      </div>
      
      <style>
        @keyframes modalIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
      </style>
      <script>
        document.querySelector('.open-modal-btn')?.addEventListener('click', () => {
          document.querySelector('.modal-overlay').style.display = 'flex';
        });
        document.querySelectorAll('.close-modal-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            document.querySelector('.modal-overlay').style.display = 'none';
          });
        });
        document.querySelector('.modal-overlay')?.addEventListener('click', (e) => {
          if (e.target.classList.contains('modal-overlay')) {
            e.target.style.display = 'none';
          }
        });
      </script>
    `
  });

  // ===========================================
  // CATÉGORIE: COMPTEURS / STATISTIQUES
  // ===========================================

  // Compteur animé
  bm.add('counter', {
    label: '🔢 Compteurs animés',
    category: 'Statistiques',
    content: `
      <section style="padding:80px 40px;background:#1f2937;">
        <div style="display:flex;justify-content:center;gap:80px;flex-wrap:wrap;">
          <div style="text-align:center;">
            <div class="counter" data-target="500" style="font-size:56px;font-weight:800;color:#3b82f6;">0</div>
            <div style="color:#9ca3af;font-size:16px;margin-top:8px;">Clients satisfaits</div>
          </div>
          <div style="text-align:center;">
            <div class="counter" data-target="50" style="font-size:56px;font-weight:800;color:#10b981;">0</div>
            <div style="color:#9ca3af;font-size:16px;margin-top:8px;">Projets réalisés</div>
          </div>
          <div style="text-align:center;">
            <div class="counter" data-target="15" style="font-size:56px;font-weight:800;color:#f59e0b;">0</div>
            <div style="color:#9ca3af;font-size:16px;margin-top:8px;">Années d'expérience</div>
          </div>
          <div style="text-align:center;">
            <div class="counter" data-target="99" style="font-size:56px;font-weight:800;color:#ef4444;">0</div>
            <div style="color:#9ca3af;font-size:16px;margin-top:8px;">% Satisfaction</div>
          </div>
        </div>
      </section>
      <script>
        const counters = document.querySelectorAll('.counter');
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const counter = entry.target;
              const target = parseInt(counter.dataset.target);
              let count = 0;
              const increment = target / 50;
              const updateCount = () => {
                if (count < target) {
                  count += increment;
                  counter.textContent = Math.ceil(count);
                  requestAnimationFrame(updateCount);
                } else {
                  counter.textContent = target;
                }
              };
              updateCount();
              observer.unobserve(counter);
            }
          });
        }, { threshold: 0.5 });
        counters.forEach(c => observer.observe(c));
      </script>
    `
  });

  // ===========================================
  // CATÉGORIE: TIMELINE
  // ===========================================

  // Timeline verticale
  bm.add('timeline', {
    label: '📅 Timeline',
    category: 'Contenu',
    content: `
      <section style="padding:80px 40px;background:#fff;">
        <h2 style="text-align:center;font-size:36px;color:#1f2937;margin:0 0 60px 0;">Notre histoire</h2>
        <div style="max-width:600px;margin:0 auto;position:relative;padding-left:40px;">
          <div style="position:absolute;left:15px;top:0;bottom:0;width:2px;background:#e5e7eb;"></div>
          
          <div style="position:relative;margin-bottom:40px;">
            <div style="position:absolute;left:-40px;width:30px;height:30px;background:#3b82f6;border-radius:50%;border:4px solid #fff;box-shadow:0 0 0 2px #3b82f6;"></div>
            <div style="background:#f9fafb;padding:24px;border-radius:12px;">
              <span style="color:#3b82f6;font-weight:600;">2020</span>
              <h4 style="color:#1f2937;margin:8px 0;">Création de l'entreprise</h4>
              <p style="color:#6b7280;margin:0;line-height:1.7;">Lancement de notre aventure entrepreneuriale.</p>
            </div>
          </div>
          
          <div style="position:relative;margin-bottom:40px;">
            <div style="position:absolute;left:-40px;width:30px;height:30px;background:#10b981;border-radius:50%;border:4px solid #fff;box-shadow:0 0 0 2px #10b981;"></div>
            <div style="background:#f9fafb;padding:24px;border-radius:12px;">
              <span style="color:#10b981;font-weight:600;">2022</span>
              <h4 style="color:#1f2937;margin:8px 0;">100ème client</h4>
              <p style="color:#6b7280;margin:0;line-height:1.7;">Nous atteignons notre 100ème client fidèle.</p>
            </div>
          </div>
          
          <div style="position:relative;">
            <div style="position:absolute;left:-40px;width:30px;height:30px;background:#f59e0b;border-radius:50%;border:4px solid #fff;box-shadow:0 0 0 2px #f59e0b;"></div>
            <div style="background:#f9fafb;padding:24px;border-radius:12px;">
              <span style="color:#f59e0b;font-weight:600;">2024</span>
              <h4 style="color:#1f2937;margin:8px 0;">Expansion internationale</h4>
              <p style="color:#6b7280;margin:0;line-height:1.7;">Ouverture de bureaux en Europe.</p>
            </div>
          </div>
        </div>
      </section>
    `
  });

  // ===========================================
  // CATÉGORIE: CARTES
  // ===========================================

  // Carte avec hover effect
  bm.add('card-hover', {
    label: '✨ Carte animée',
    category: 'Cartes',
    content: `
      <div class="hover-card" style="max-width:350px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);transition:transform 0.3s, box-shadow 0.3s;cursor:pointer;">
        <div style="height:200px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;">
          <span style="font-size:60px;">🎨</span>
        </div>
        <div style="padding:30px;">
          <h3 style="color:#1f2937;margin:0 0 12px 0;font-size:22px;">Titre de la carte</h3>
          <p style="color:#6b7280;line-height:1.7;margin:0 0 20px 0;">Description courte de l'élément avec un effet de survol élégant.</p>
          <a href="#" style="color:#667eea;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:8px;">
            En savoir plus <span>→</span>
          </a>
        </div>
      </div>
      <style>
        .hover-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(0,0,0,0.15); }
      </style>
    `
  });

  // Carte profil
  bm.add('card-profile', {
    label: '👤 Carte profil',
    category: 'Cartes',
    content: `
      <div style="max-width:300px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.1);text-align:center;">
        <div style="height:80px;background:linear-gradient(135deg,#3b82f6 0%,#8b5cf6 100%);"></div>
        <div style="margin-top:-50px;padding:0 30px 30px;">
          <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200" style="width:100px;height:100px;border-radius:50%;border:4px solid #fff;object-fit:cover;">
          <h3 style="color:#1f2937;margin:16px 0 4px 0;">Jean Dupont</h3>
          <p style="color:#6b7280;margin:0 0 20px 0;font-size:14px;">Designer UX/UI</p>
          <div style="display:flex;justify-content:center;gap:12px;">
            <a href="#" style="width:36px;height:36px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;">🐦</a>
            <a href="#" style="width:36px;height:36px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;">💼</a>
            <a href="#" style="width:36px;height:36px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;">📧</a>
          </div>
        </div>
      </div>
    `
  });
}

export default registerAdvancedBlocks;
