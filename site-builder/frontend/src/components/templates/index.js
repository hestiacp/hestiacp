/**
 * ===========================================
 * Templates de sites complets
 * ===========================================
 * 
 * Collection de templates prêts à l'emploi pour différents secteurs.
 */

export const siteTemplates = {
  // ===========================================
  // TEMPLATE: RESTAURANT
  // ===========================================
  restaurant: {
    name: 'Restaurant',
    description: 'Template pour restaurants et cafés',
    thumbnail: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400',
    pages: [
      {
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        content: `
          <nav style="position:fixed;top:0;left:0;right:0;z-index:100;display:flex;justify-content:space-between;align-items:center;padding:20px 40px;background:rgba(0,0,0,0.8);">
            <div style="font-size:28px;font-weight:bold;color:#d4a574;font-family:Georgia,serif;">La Belle Table</div>
            <div style="display:flex;gap:30px;">
              <a href="index.html" style="color:#fff;text-decoration:none;">Accueil</a>
              <a href="menu.html" style="color:#fff;text-decoration:none;">Menu</a>
              <a href="about.html" style="color:#fff;text-decoration:none;">Notre Histoire</a>
              <a href="contact.html" style="color:#fff;text-decoration:none;">Réservation</a>
            </div>
          </nav>
          
          <section style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;background:linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920') center/cover;">
            <div style="max-width:800px;padding:20px;">
              <h1 style="font-size:72px;color:#fff;font-family:Georgia,serif;margin:0 0 20px 0;">Bienvenue</h1>
              <p style="font-size:24px;color:#d4a574;margin:0 0 40px 0;">Une expérience culinaire unique depuis 1985</p>
              <a href="contact.html" style="display:inline-block;background:#d4a574;color:#000;padding:18px 50px;font-size:18px;text-decoration:none;font-weight:600;">Réserver une table</a>
            </div>
          </section>
          
          <section style="padding:100px 40px;background:#1a1a1a;">
            <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
              <div style="text-align:center;padding:40px;">
                <div style="font-size:48px;margin-bottom:20px;">🍽️</div>
                <h3 style="color:#d4a574;font-size:24px;margin:0 0 16px 0;">Cuisine raffinée</h3>
                <p style="color:#999;line-height:1.8;">Des plats préparés avec passion par notre chef étoilé.</p>
              </div>
              <div style="text-align:center;padding:40px;">
                <div style="font-size:48px;margin-bottom:20px;">🍷</div>
                <h3 style="color:#d4a574;font-size:24px;margin:0 0 16px 0;">Cave exceptionnelle</h3>
                <p style="color:#999;line-height:1.8;">Plus de 200 références sélectionnées par notre sommelier.</p>
              </div>
              <div style="text-align:center;padding:40px;">
                <div style="font-size:48px;margin-bottom:20px;">✨</div>
                <h3 style="color:#d4a574;font-size:24px;margin:0 0 16px 0;">Ambiance unique</h3>
                <p style="color:#999;line-height:1.8;">Un cadre élégant pour vos moments les plus précieux.</p>
              </div>
            </div>
          </section>
          
          <footer style="padding:60px 40px;background:#111;text-align:center;">
            <div style="color:#d4a574;font-size:24px;font-family:Georgia,serif;margin-bottom:20px;">La Belle Table</div>
            <p style="color:#666;margin:0;">123 Avenue des Gourmets, Paris • 01 23 45 67 89</p>
            <p style="color:#666;margin-top:20px;">© 2024 La Belle Table. Tous droits réservés.</p>
          </footer>
        `
      },
      {
        name: 'Menu',
        slug: 'menu',
        content: `
          <section style="padding:120px 40px 80px;background:#1a1a1a;text-align:center;">
            <h1 style="font-size:48px;color:#d4a574;font-family:Georgia,serif;margin:0 0 20px 0;">Notre Menu</h1>
            <p style="color:#999;font-size:18px;">Découvrez nos créations du moment</p>
          </section>
          <section style="padding:80px 40px;background:#111;">
            <div style="max-width:800px;margin:0 auto;">
              <h2 style="color:#d4a574;font-size:32px;text-align:center;margin-bottom:40px;">Entrées</h2>
              <div style="border-bottom:1px solid #333;padding:20px 0;display:flex;justify-content:space-between;">
                <div><h4 style="color:#fff;margin:0;">Foie gras maison</h4><p style="color:#666;margin:5px 0 0 0;">Chutney de figues et pain brioché</p></div>
                <span style="color:#d4a574;font-size:20px;">24€</span>
              </div>
              <div style="border-bottom:1px solid #333;padding:20px 0;display:flex;justify-content:space-between;">
                <div><h4 style="color:#fff;margin:0;">Tartare de saumon</h4><p style="color:#666;margin:5px 0 0 0;">Avocat, citron vert et coriandre</p></div>
                <span style="color:#d4a574;font-size:20px;">18€</span>
              </div>
            </div>
          </section>
        `
      }
    ],
    settings: {
      colors: {
        primary: '#d4a574',
        secondary: '#1a1a1a',
        text: '#ffffff',
        background: '#111111'
      },
      fonts: {
        heading: 'Georgia, serif',
        body: 'Inter, sans-serif'
      }
    }
  },

  // ===========================================
  // TEMPLATE: SAAS / STARTUP
  // ===========================================
  saas: {
    name: 'SaaS / Startup',
    description: 'Template moderne pour applications SaaS',
    thumbnail: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400',
    pages: [
      {
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        content: `
          <nav style="display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size:24px;font-weight:800;color:#6366f1;">AppFlow</div>
            <div style="display:flex;gap:40px;align-items:center;">
              <a href="#features" style="color:#64748b;text-decoration:none;font-weight:500;">Fonctionnalités</a>
              <a href="#pricing" style="color:#64748b;text-decoration:none;font-weight:500;">Tarifs</a>
              <a href="#" style="color:#64748b;text-decoration:none;font-weight:500;">Blog</a>
              <a href="#" style="background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;">Essai gratuit</a>
            </div>
          </nav>
          
          <section style="padding:120px 60px;background:linear-gradient(135deg,#f8fafc 0%,#e0e7ff 100%);">
            <div style="max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:80px;">
              <div style="flex:1;">
                <span style="background:#e0e7ff;color:#6366f1;padding:8px 16px;border-radius:20px;font-size:14px;font-weight:600;">🚀 Nouveau: IA intégrée</span>
                <h1 style="font-size:56px;font-weight:800;color:#1e293b;line-height:1.1;margin:24px 0;">Automatisez votre workflow en quelques clics</h1>
                <p style="font-size:20px;color:#64748b;line-height:1.8;margin:0 0 40px 0;">AppFlow connecte tous vos outils et automatise vos tâches répétitives. Gagnez 10h par semaine.</p>
                <div style="display:flex;gap:16px;">
                  <a href="#" style="background:#6366f1;color:#fff;padding:16px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:16px;">Démarrer gratuitement</a>
                  <a href="#" style="background:#fff;color:#1e293b;padding:16px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:16px;border:1px solid #e2e8f0;">Voir la démo</a>
                </div>
                <p style="color:#94a3b8;font-size:14px;margin-top:20px;">✓ 14 jours d'essai gratuit • ✓ Sans carte bancaire</p>
              </div>
              <div style="flex:1;">
                <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?w=600" style="width:100%;border-radius:16px;box-shadow:0 25px 50px rgba(0,0,0,0.15);">
              </div>
            </div>
          </section>
          
          <section id="features" style="padding:100px 60px;background:#fff;">
            <div style="text-align:center;max-width:600px;margin:0 auto 60px;">
              <h2 style="font-size:40px;font-weight:800;color:#1e293b;margin:0 0 16px 0;">Tout ce dont vous avez besoin</h2>
              <p style="font-size:18px;color:#64748b;">Des fonctionnalités puissantes pour booster votre productivité</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;max-width:1200px;margin:0 auto;">
              <div style="padding:40px;background:#f8fafc;border-radius:16px;">
                <div style="width:60px;height:60px;background:#e0e7ff;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;font-size:28px;">⚡</div>
                <h3 style="font-size:22px;font-weight:600;color:#1e293b;margin:0 0 12px 0;">Automatisation</h3>
                <p style="color:#64748b;line-height:1.7;margin:0;">Créez des workflows automatisés sans coder.</p>
              </div>
              <div style="padding:40px;background:#f8fafc;border-radius:16px;">
                <div style="width:60px;height:60px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;font-size:28px;">🔗</div>
                <h3 style="font-size:22px;font-weight:600;color:#1e293b;margin:0 0 12px 0;">Intégrations</h3>
                <p style="color:#64748b;line-height:1.7;margin:0;">Connectez +200 applications en un clic.</p>
              </div>
              <div style="padding:40px;background:#f8fafc;border-radius:16px;">
                <div style="width:60px;height:60px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;font-size:28px;">📊</div>
                <h3 style="font-size:22px;font-weight:600;color:#1e293b;margin:0 0 12px 0;">Analytics</h3>
                <p style="color:#64748b;line-height:1.7;margin:0;">Suivez vos performances en temps réel.</p>
              </div>
            </div>
          </section>
          
          <section style="padding:80px 60px;background:#6366f1;text-align:center;">
            <h2 style="font-size:40px;font-weight:800;color:#fff;margin:0 0 16px 0;">Prêt à transformer votre entreprise ?</h2>
            <p style="font-size:20px;color:rgba(255,255,255,0.8);margin:0 0 40px 0;">Rejoignez +10,000 entreprises qui nous font confiance</p>
            <a href="#" style="display:inline-block;background:#fff;color:#6366f1;padding:18px 40px;border-radius:8px;text-decoration:none;font-weight:600;font-size:18px;">Commencer maintenant</a>
          </section>
          
          <footer style="padding:60px;background:#0f172a;">
            <div style="display:flex;justify-content:space-between;max-width:1200px;margin:0 auto;">
              <div>
                <div style="font-size:24px;font-weight:800;color:#fff;margin-bottom:16px;">AppFlow</div>
                <p style="color:#64748b;max-width:300px;">La plateforme d'automatisation pour les équipes modernes.</p>
              </div>
              <div style="display:flex;gap:80px;">
                <div>
                  <h4 style="color:#fff;margin:0 0 16px 0;">Produit</h4>
                  <a href="#" style="display:block;color:#64748b;text-decoration:none;margin-bottom:8px;">Fonctionnalités</a>
                  <a href="#" style="display:block;color:#64748b;text-decoration:none;margin-bottom:8px;">Tarifs</a>
                </div>
                <div>
                  <h4 style="color:#fff;margin:0 0 16px 0;">Entreprise</h4>
                  <a href="#" style="display:block;color:#64748b;text-decoration:none;margin-bottom:8px;">À propos</a>
                  <a href="#" style="display:block;color:#64748b;text-decoration:none;">Contact</a>
                </div>
              </div>
            </div>
          </footer>
        `
      }
    ],
    settings: {
      colors: {
        primary: '#6366f1',
        secondary: '#0f172a',
        text: '#1e293b',
        background: '#ffffff'
      }
    }
  },

  // ===========================================
  // TEMPLATE: PORTFOLIO
  // ===========================================
  portfolio: {
    name: 'Portfolio',
    description: 'Template pour créatifs et freelances',
    thumbnail: 'https://images.unsplash.com/photo-1545665277-5937489579f2?w=400',
    pages: [
      {
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        content: `
          <nav style="position:fixed;top:0;left:0;right:0;z-index:100;display:flex;justify-content:space-between;align-items:center;padding:20px 40px;background:rgba(10,10,10,0.95);">
            <div style="font-size:20px;font-weight:600;color:#fff;">JOHN DOE</div>
            <div style="display:flex;gap:30px;">
              <a href="#work" style="color:#fff;text-decoration:none;font-size:14px;letter-spacing:1px;">PROJETS</a>
              <a href="#about" style="color:#fff;text-decoration:none;font-size:14px;letter-spacing:1px;">À PROPOS</a>
              <a href="#contact" style="color:#fff;text-decoration:none;font-size:14px;letter-spacing:1px;">CONTACT</a>
            </div>
          </nav>
          
          <section style="min-height:100vh;display:flex;align-items:center;padding:0 80px;background:#0a0a0a;">
            <div style="max-width:800px;">
              <p style="color:#666;font-size:14px;letter-spacing:3px;margin:0 0 20px 0;">DESIGNER & DÉVELOPPEUR</p>
              <h1 style="font-size:80px;font-weight:800;color:#fff;line-height:1;margin:0 0 30px 0;">Créateur d'expériences digitales</h1>
              <p style="font-size:20px;color:#888;line-height:1.8;margin:0 0 40px 0;">Je conçois des sites web et applications qui allient esthétique et performance.</p>
              <a href="#work" style="display:inline-block;border:2px solid #fff;color:#fff;padding:16px 40px;text-decoration:none;font-size:14px;letter-spacing:2px;transition:all 0.3s;">VOIR MES PROJETS</a>
            </div>
          </section>
          
          <section id="work" style="padding:100px 40px;background:#111;">
            <h2 style="font-size:14px;color:#666;letter-spacing:3px;text-align:center;margin:0 0 60px 0;">PROJETS SÉLECTIONNÉS</h2>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:30px;max-width:1200px;margin:0 auto;">
              <div style="position:relative;overflow:hidden;aspect-ratio:16/10;">
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;bottom:0;left:0;right:0;padding:30px;background:linear-gradient(transparent,rgba(0,0,0,0.9));">
                  <h3 style="color:#fff;margin:0;font-size:24px;">Projet Alpha</h3>
                  <p style="color:#888;margin:8px 0 0 0;">Web Design</p>
                </div>
              </div>
              <div style="position:relative;overflow:hidden;aspect-ratio:16/10;">
                <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;bottom:0;left:0;right:0;padding:30px;background:linear-gradient(transparent,rgba(0,0,0,0.9));">
                  <h3 style="color:#fff;margin:0;font-size:24px;">Projet Beta</h3>
                  <p style="color:#888;margin:8px 0 0 0;">Application Mobile</p>
                </div>
              </div>
            </div>
          </section>
          
          <section id="contact" style="padding:100px 40px;background:#0a0a0a;text-align:center;">
            <h2 style="font-size:48px;color:#fff;margin:0 0 20px 0;">Travaillons ensemble</h2>
            <p style="color:#888;font-size:18px;margin:0 0 40px 0;">Vous avez un projet ? Discutons-en.</p>
            <a href="mailto:hello@johndoe.com" style="display:inline-block;background:#fff;color:#000;padding:18px 50px;text-decoration:none;font-weight:600;">Me contacter</a>
          </section>
        `
      }
    ],
    settings: {
      colors: {
        primary: '#ffffff',
        secondary: '#0a0a0a',
        text: '#ffffff',
        background: '#0a0a0a'
      }
    }
  },

  // ===========================================
  // TEMPLATE: AVOCAT / CABINET
  // ===========================================
  lawyer: {
    name: 'Avocat / Cabinet',
    description: 'Template professionnel pour avocats',
    thumbnail: 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=400',
    pages: [
      {
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        content: `
          <nav style="display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:#1e3a5f;">
            <div style="font-size:22px;font-weight:600;color:#c9a227;">CABINET MARTIN</div>
            <div style="display:flex;gap:30px;">
              <a href="#" style="color:#fff;text-decoration:none;">Accueil</a>
              <a href="#" style="color:#fff;text-decoration:none;">Expertises</a>
              <a href="#" style="color:#fff;text-decoration:none;">L'équipe</a>
              <a href="#" style="color:#fff;text-decoration:none;">Contact</a>
            </div>
          </nav>
          
          <section style="min-height:600px;display:flex;align-items:center;padding:80px 60px;background:linear-gradient(rgba(30,58,95,0.9),rgba(30,58,95,0.9)),url('https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1920') center/cover;">
            <div style="max-width:700px;">
              <h1 style="font-size:52px;font-weight:700;color:#fff;line-height:1.2;margin:0 0 24px 0;">Votre droit, notre engagement</h1>
              <p style="font-size:20px;color:rgba(255,255,255,0.8);line-height:1.8;margin:0 0 40px 0;">Cabinet d'avocats spécialisé en droit des affaires, droit immobilier et droit de la famille depuis 1995.</p>
              <a href="#contact" style="display:inline-block;background:#c9a227;color:#1e3a5f;padding:16px 40px;text-decoration:none;font-weight:600;">Prendre rendez-vous</a>
            </div>
          </section>
          
          <section style="padding:80px 60px;background:#fff;">
            <div style="text-align:center;margin-bottom:60px;">
              <h2 style="font-size:36px;color:#1e3a5f;margin:0 0 16px 0;">Nos domaines d'expertise</h2>
              <p style="color:#666;">Une équipe pluridisciplinaire à votre service</p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;max-width:1000px;margin:0 auto;">
              <div style="text-align:center;padding:40px;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="font-size:40px;margin-bottom:20px;">⚖️</div>
                <h3 style="color:#1e3a5f;margin:0 0 12px 0;">Droit des affaires</h3>
                <p style="color:#666;line-height:1.7;margin:0;">Création, gestion et transmission d'entreprise.</p>
              </div>
              <div style="text-align:center;padding:40px;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="font-size:40px;margin-bottom:20px;">🏠</div>
                <h3 style="color:#1e3a5f;margin:0 0 12px 0;">Droit immobilier</h3>
                <p style="color:#666;line-height:1.7;margin:0;">Transactions, copropriété, baux commerciaux.</p>
              </div>
              <div style="text-align:center;padding:40px;border:1px solid #e5e7eb;border-radius:8px;">
                <div style="font-size:40px;margin-bottom:20px;">👨‍👩‍👧</div>
                <h3 style="color:#1e3a5f;margin:0 0 12px 0;">Droit de la famille</h3>
                <p style="color:#666;line-height:1.7;margin:0;">Divorce, garde d'enfants, successions.</p>
              </div>
            </div>
          </section>
          
          <footer style="padding:40px 60px;background:#1e3a5f;text-align:center;">
            <p style="color:rgba(255,255,255,0.7);margin:0;">© 2024 Cabinet Martin - Avocats au Barreau de Paris</p>
          </footer>
        `
      }
    ],
    settings: {
      colors: {
        primary: '#c9a227',
        secondary: '#1e3a5f',
        text: '#1e3a5f',
        background: '#ffffff'
      }
    }
  },

  // ===========================================
  // TEMPLATE: COACH / CONSULTANT
  // ===========================================
  coach: {
    name: 'Coach / Consultant',
    description: 'Template pour coachs et consultants',
    thumbnail: 'https://images.unsplash.com/photo-1552581234-26160f608093?w=400',
    pages: [
      {
        name: 'Accueil',
        slug: 'index',
        is_homepage: true,
        content: `
          <nav style="display:flex;justify-content:space-between;align-items:center;padding:20px 60px;background:#fff;">
            <div style="font-size:24px;font-weight:700;color:#059669;">Sophie Laurent</div>
            <div style="display:flex;gap:30px;align-items:center;">
              <a href="#" style="color:#374151;text-decoration:none;">Services</a>
              <a href="#" style="color:#374151;text-decoration:none;">À propos</a>
              <a href="#" style="color:#374151;text-decoration:none;">Témoignages</a>
              <a href="#" style="background:#059669;color:#fff;padding:12px 28px;border-radius:30px;text-decoration:none;">Réserver</a>
            </div>
          </nav>
          
          <section style="display:flex;min-height:600px;background:#f0fdf4;">
            <div style="flex:1;padding:100px 60px;display:flex;flex-direction:column;justify-content:center;">
              <h1 style="font-size:52px;font-weight:800;color:#166534;line-height:1.1;margin:0 0 24px 0;">Révélez votre potentiel</h1>
              <p style="font-size:20px;color:#4b5563;line-height:1.8;margin:0 0 40px 0;">Coach certifiée en développement personnel et professionnel. Je vous accompagne vers une vie épanouie et alignée avec vos valeurs.</p>
              <div style="display:flex;gap:16px;">
                <a href="#" style="background:#059669;color:#fff;padding:16px 32px;border-radius:30px;text-decoration:none;font-weight:600;">Séance découverte gratuite</a>
              </div>
            </div>
            <div style="flex:1;background:url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800') center/cover;"></div>
          </section>
          
          <section style="padding:80px 60px;background:#fff;">
            <h2 style="text-align:center;font-size:36px;color:#166534;margin:0 0 60px 0;">Mes accompagnements</h2>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:30px;max-width:1000px;margin:0 auto;">
              <div style="padding:40px;background:#f0fdf4;border-radius:16px;text-align:center;">
                <h3 style="color:#166534;margin:0 0 16px 0;">Coaching individuel</h3>
                <p style="color:#4b5563;line-height:1.7;margin:0 0 20px 0;">Séances personnalisées pour atteindre vos objectifs.</p>
                <span style="color:#059669;font-weight:600;">À partir de 90€/h</span>
              </div>
              <div style="padding:40px;background:#f0fdf4;border-radius:16px;text-align:center;">
                <h3 style="color:#166534;margin:0 0 16px 0;">Programme 3 mois</h3>
                <p style="color:#4b5563;line-height:1.7;margin:0 0 20px 0;">Transformation profonde avec suivi régulier.</p>
                <span style="color:#059669;font-weight:600;">1 500€</span>
              </div>
              <div style="padding:40px;background:#f0fdf4;border-radius:16px;text-align:center;">
                <h3 style="color:#166534;margin:0 0 16px 0;">Atelier collectif</h3>
                <p style="color:#4b5563;line-height:1.7;margin:0 0 20px 0;">Sessions de groupe pour progresser ensemble.</p>
                <span style="color:#059669;font-weight:600;">45€/personne</span>
              </div>
            </div>
          </section>
          
          <footer style="padding:40px;background:#166534;text-align:center;">
            <p style="color:rgba(255,255,255,0.8);margin:0;">© 2024 Sophie Laurent - Coach certifiée ICF</p>
          </footer>
        `
      }
    ],
    settings: {
      colors: {
        primary: '#059669',
        secondary: '#166534',
        text: '#166534',
        background: '#f0fdf4'
      }
    }
  }
};

/**
 * Récupère un template par son ID
 */
export function getTemplate(templateId) {
  return siteTemplates[templateId] || null;
}

/**
 * Liste tous les templates disponibles
 */
export function listTemplates() {
  return Object.entries(siteTemplates).map(([id, template]) => ({
    id,
    name: template.name,
    description: template.description,
    thumbnail: template.thumbnail
  }));
}

export default siteTemplates;
