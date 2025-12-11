/**
 * ===========================================
 * Blocs personnalisés GrapesJS
 * ===========================================
 * 
 * Définit les blocs custom pour le Site Builder :
 * - Header (navigation)
 * - Hero section
 * - Section texte + image
 * - Galerie
 * - Formulaire de contact
 * - Footer
 * - Et plus encore...
 * 
 * Ces blocs sont conçus pour être responsive et faciles à personnaliser.
 */

/**
 * Enregistre tous les blocs personnalisés dans GrapesJS
 * @param {Editor} editor - Instance GrapesJS
 */
export function registerCustomBlocks(editor) {
  const blockManager = editor.BlockManager;

  // Ajouter une catégorie pour nos blocs
  const categoryLabel = 'Sections';

  // ===========================================
  // BLOC HEADER / NAVIGATION
  // ===========================================
  blockManager.add('header-block', {
    label: 'Header',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="4" width="20" height="4" rx="1"/>
      <circle cx="5" cy="6" r="1.5"/>
      <rect x="14" y="5" width="3" height="2" rx="0.5"/>
      <rect x="18" y="5" width="3" height="2" rx="0.5"/>
    </svg>`,
    content: `
      <header class="site-header" data-gjs-type="header">
        <div class="header-container">
          <div class="header-logo">
            <a href="/">Mon Site</a>
          </div>
          <nav class="header-nav">
            <a href="/" class="nav-link">Accueil</a>
            <a href="/about.html" class="nav-link">À propos</a>
            <a href="/services.html" class="nav-link">Services</a>
            <a href="/contact.html" class="nav-link">Contact</a>
          </nav>
          <div class="header-cta">
            <a href="/contact.html" class="btn-cta">Nous contacter</a>
          </div>
        </div>
      </header>
      <style>
        .site-header {
          background-color: #ffffff;
          box-shadow: 0 1px 3px rgba(0,0,0,0.1);
          position: sticky;
          top: 0;
          z-index: 100;
        }
        .header-container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 16px 24px;
          display: flex;
          align-items: center;
          justify-content: space-between;
        }
        .header-logo a {
          font-size: 1.5rem;
          font-weight: 700;
          color: #1f2937;
          text-decoration: none;
        }
        .header-nav {
          display: flex;
          gap: 32px;
        }
        .nav-link {
          color: #4b5563;
          text-decoration: none;
          font-weight: 500;
          transition: color 0.2s;
        }
        .nav-link:hover {
          color: #3b82f6;
        }
        .btn-cta {
          background-color: #3b82f6;
          color: white;
          padding: 10px 20px;
          border-radius: 6px;
          text-decoration: none;
          font-weight: 500;
          transition: background-color 0.2s;
        }
        .btn-cta:hover {
          background-color: #2563eb;
        }
        @media (max-width: 768px) {
          .header-nav { display: none; }
          .header-cta { display: none; }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC HERO SECTION
  // ===========================================
  blockManager.add('hero-block', {
    label: 'Hero Section',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="3" width="20" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/>
      <text x="12" y="10" font-size="4" text-anchor="middle" font-weight="bold">HERO</text>
      <rect x="8" y="13" width="8" height="3" rx="1"/>
    </svg>`,
    content: `
      <section class="hero-section" data-gjs-type="hero">
        <div class="hero-container">
          <div class="hero-content">
            <h1 class="hero-title">Bienvenue sur notre site</h1>
            <p class="hero-subtitle">Une description accrocheuse de votre entreprise ou projet. Captivez vos visiteurs dès la première seconde.</p>
            <div class="hero-buttons">
              <a href="#" class="btn btn-primary">Commencer</a>
              <a href="#" class="btn btn-secondary">En savoir plus</a>
            </div>
          </div>
          <div class="hero-image">
            <img src="https://placehold.co/600x400/3b82f6/ffffff?text=Image+Hero" alt="Hero image"/>
          </div>
        </div>
      </section>
      <style>
        .hero-section {
          background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
          padding: 80px 24px;
        }
        .hero-container {
          max-width: 1200px;
          margin: 0 auto;
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 48px;
          align-items: center;
        }
        .hero-title {
          font-size: 3rem;
          font-weight: 700;
          color: #1f2937;
          line-height: 1.2;
          margin-bottom: 24px;
        }
        .hero-subtitle {
          font-size: 1.25rem;
          color: #6b7280;
          line-height: 1.6;
          margin-bottom: 32px;
        }
        .hero-buttons {
          display: flex;
          gap: 16px;
        }
        .hero-image img {
          width: 100%;
          border-radius: 12px;
          box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .btn {
          padding: 14px 28px;
          border-radius: 8px;
          font-weight: 600;
          text-decoration: none;
          transition: all 0.2s;
        }
        .btn-primary {
          background-color: #3b82f6;
          color: white;
        }
        .btn-primary:hover {
          background-color: #2563eb;
        }
        .btn-secondary {
          background-color: white;
          color: #1f2937;
          border: 2px solid #e5e7eb;
        }
        .btn-secondary:hover {
          border-color: #3b82f6;
          color: #3b82f6;
        }
        @media (max-width: 768px) {
          .hero-container {
            grid-template-columns: 1fr;
          }
          .hero-title {
            font-size: 2rem;
          }
          .hero-image {
            order: -1;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC SECTION TEXTE + IMAGE
  // ===========================================
  blockManager.add('text-image-block', {
    label: 'Texte + Image',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="4" width="9" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/>
      <rect x="13" y="4" width="9" height="16" rx="1"/>
      <line x1="4" y1="8" x2="9" y2="8" stroke="currentColor" stroke-width="1"/>
      <line x1="4" y1="11" x2="9" y2="11" stroke="currentColor" stroke-width="1"/>
      <line x1="4" y1="14" x2="7" y2="14" stroke="currentColor" stroke-width="1"/>
    </svg>`,
    content: `
      <section class="text-image-section" data-gjs-type="text-image">
        <div class="ti-container">
          <div class="ti-content">
            <span class="ti-badge">À propos</span>
            <h2 class="ti-title">Notre histoire</h2>
            <p class="ti-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
            <p class="ti-text">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            <a href="#" class="ti-link">En savoir plus →</a>
          </div>
          <div class="ti-image">
            <img src="https://placehold.co/500x400/10b981/ffffff?text=Image" alt="Image"/>
          </div>
        </div>
      </section>
      <style>
        .text-image-section {
          padding: 80px 24px;
          background-color: #ffffff;
        }
        .ti-container {
          max-width: 1200px;
          margin: 0 auto;
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 64px;
          align-items: center;
        }
        .ti-badge {
          display: inline-block;
          background-color: #dbeafe;
          color: #3b82f6;
          padding: 6px 12px;
          border-radius: 20px;
          font-size: 0.875rem;
          font-weight: 600;
          margin-bottom: 16px;
        }
        .ti-title {
          font-size: 2.25rem;
          font-weight: 700;
          color: #1f2937;
          margin-bottom: 24px;
        }
        .ti-text {
          color: #6b7280;
          line-height: 1.8;
          margin-bottom: 16px;
        }
        .ti-link {
          color: #3b82f6;
          font-weight: 600;
          text-decoration: none;
        }
        .ti-link:hover {
          text-decoration: underline;
        }
        .ti-image img {
          width: 100%;
          border-radius: 12px;
        }
        @media (max-width: 768px) {
          .ti-container {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC GALERIE
  // ===========================================
  blockManager.add('gallery-block', {
    label: 'Galerie',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="2" width="6" height="6" rx="1"/>
      <rect x="9" y="2" width="6" height="6" rx="1"/>
      <rect x="16" y="2" width="6" height="6" rx="1"/>
      <rect x="2" y="9" width="6" height="6" rx="1"/>
      <rect x="9" y="9" width="6" height="6" rx="1"/>
      <rect x="16" y="9" width="6" height="6" rx="1"/>
    </svg>`,
    content: `
      <section class="gallery-section" data-gjs-type="gallery">
        <div class="gallery-container">
          <div class="gallery-header">
            <h2 class="gallery-title">Notre galerie</h2>
            <p class="gallery-subtitle">Découvrez nos réalisations et projets</p>
          </div>
          <div class="gallery-grid">
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/6366f1/ffffff?text=Image+1" alt="Galerie 1"/>
              <div class="gallery-overlay">
                <span>Projet 1</span>
              </div>
            </div>
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/8b5cf6/ffffff?text=Image+2" alt="Galerie 2"/>
              <div class="gallery-overlay">
                <span>Projet 2</span>
              </div>
            </div>
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/ec4899/ffffff?text=Image+3" alt="Galerie 3"/>
              <div class="gallery-overlay">
                <span>Projet 3</span>
              </div>
            </div>
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/f59e0b/ffffff?text=Image+4" alt="Galerie 4"/>
              <div class="gallery-overlay">
                <span>Projet 4</span>
              </div>
            </div>
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/10b981/ffffff?text=Image+5" alt="Galerie 5"/>
              <div class="gallery-overlay">
                <span>Projet 5</span>
              </div>
            </div>
            <div class="gallery-item">
              <img src="https://placehold.co/400x300/3b82f6/ffffff?text=Image+6" alt="Galerie 6"/>
              <div class="gallery-overlay">
                <span>Projet 6</span>
              </div>
            </div>
          </div>
        </div>
      </section>
      <style>
        .gallery-section {
          padding: 80px 24px;
          background-color: #f9fafb;
        }
        .gallery-container {
          max-width: 1200px;
          margin: 0 auto;
        }
        .gallery-header {
          text-align: center;
          margin-bottom: 48px;
        }
        .gallery-title {
          font-size: 2.25rem;
          font-weight: 700;
          color: #1f2937;
          margin-bottom: 12px;
        }
        .gallery-subtitle {
          color: #6b7280;
          font-size: 1.125rem;
        }
        .gallery-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 24px;
        }
        .gallery-item {
          position: relative;
          overflow: hidden;
          border-radius: 12px;
        }
        .gallery-item img {
          width: 100%;
          height: 250px;
          object-fit: cover;
          transition: transform 0.3s;
        }
        .gallery-item:hover img {
          transform: scale(1.1);
        }
        .gallery-overlay {
          position: absolute;
          inset: 0;
          background: rgba(0,0,0,0.5);
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0;
          transition: opacity 0.3s;
        }
        .gallery-item:hover .gallery-overlay {
          opacity: 1;
        }
        .gallery-overlay span {
          color: white;
          font-weight: 600;
          font-size: 1.125rem;
        }
        @media (max-width: 768px) {
          .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
          }
        }
        @media (max-width: 480px) {
          .gallery-grid {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC FORMULAIRE DE CONTACT
  // ===========================================
  blockManager.add('contact-form-block', {
    label: 'Contact',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="4" width="20" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/>
      <line x1="4" y1="8" x2="20" y2="8" stroke="currentColor"/>
      <line x1="4" y1="12" x2="20" y2="12" stroke="currentColor"/>
      <rect x="4" y="15" width="8" height="3" rx="1"/>
    </svg>`,
    content: `
      <section class="contact-section" data-gjs-type="contact">
        <div class="contact-container">
          <div class="contact-info">
            <h2 class="contact-title">Contactez-nous</h2>
            <p class="contact-subtitle">Nous sommes là pour vous aider. N'hésitez pas à nous écrire.</p>
            <div class="contact-details">
              <div class="contact-item">
                <span class="contact-icon">📍</span>
                <span>123 Rue Example, 75000 Paris</span>
              </div>
              <div class="contact-item">
                <span class="contact-icon">📧</span>
                <span>contact@example.com</span>
              </div>
              <div class="contact-item">
                <span class="contact-icon">📞</span>
                <span>+33 1 23 45 67 89</span>
              </div>
            </div>
          </div>
          <div class="contact-form-wrapper">
            <form class="contact-form">
              <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" placeholder="Votre nom" required/>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required/>
              </div>
              <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" id="subject" name="subject" placeholder="Objet de votre message"/>
              </div>
              <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" placeholder="Votre message..." required></textarea>
              </div>
              <button type="submit" class="form-submit">Envoyer le message</button>
            </form>
          </div>
        </div>
      </section>
      <style>
        .contact-section {
          padding: 80px 24px;
          background-color: #ffffff;
        }
        .contact-container {
          max-width: 1200px;
          margin: 0 auto;
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 64px;
        }
        .contact-title {
          font-size: 2.25rem;
          font-weight: 700;
          color: #1f2937;
          margin-bottom: 16px;
        }
        .contact-subtitle {
          color: #6b7280;
          margin-bottom: 32px;
        }
        .contact-details {
          display: flex;
          flex-direction: column;
          gap: 16px;
        }
        .contact-item {
          display: flex;
          align-items: center;
          gap: 12px;
          color: #4b5563;
        }
        .contact-icon {
          font-size: 1.25rem;
        }
        .contact-form {
          display: flex;
          flex-direction: column;
          gap: 20px;
        }
        .form-group {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        .form-group label {
          font-weight: 500;
          color: #374151;
        }
        .form-group input,
        .form-group textarea {
          padding: 12px 16px;
          border: 1px solid #d1d5db;
          border-radius: 8px;
          font-size: 1rem;
          transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-submit {
          background-color: #3b82f6;
          color: white;
          padding: 14px 28px;
          border: none;
          border-radius: 8px;
          font-size: 1rem;
          font-weight: 600;
          cursor: pointer;
          transition: background-color 0.2s;
        }
        .form-submit:hover {
          background-color: #2563eb;
        }
        @media (max-width: 768px) {
          .contact-container {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC FOOTER
  // ===========================================
  blockManager.add('footer-block', {
    label: 'Footer',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="16" width="20" height="4" rx="1"/>
      <line x1="6" y1="18" x2="10" y2="18" stroke="white" stroke-width="1"/>
      <line x1="14" y1="18" x2="18" y2="18" stroke="white" stroke-width="1"/>
    </svg>`,
    content: `
      <footer class="site-footer" data-gjs-type="footer">
        <div class="footer-container">
          <div class="footer-main">
            <div class="footer-brand">
              <h3 class="footer-logo">Mon Site</h3>
              <p class="footer-description">Une description courte de votre entreprise ou projet.</p>
              <div class="footer-social">
                <a href="#" class="social-link">FB</a>
                <a href="#" class="social-link">TW</a>
                <a href="#" class="social-link">IG</a>
                <a href="#" class="social-link">LN</a>
              </div>
            </div>
            <div class="footer-links">
              <h4>Navigation</h4>
              <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/about.html">À propos</a></li>
                <li><a href="/services.html">Services</a></li>
                <li><a href="/contact.html">Contact</a></li>
              </ul>
            </div>
            <div class="footer-links">
              <h4>Légal</h4>
              <ul>
                <li><a href="#">Mentions légales</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
                <li><a href="#">CGV</a></li>
              </ul>
            </div>
            <div class="footer-links">
              <h4>Contact</h4>
              <ul>
                <li>123 Rue Example</li>
                <li>75000 Paris, France</li>
                <li>contact@example.com</li>
                <li>+33 1 23 45 67 89</li>
              </ul>
            </div>
          </div>
          <div class="footer-bottom">
            <p>© 2024 Mon Site. Tous droits réservés.</p>
          </div>
        </div>
      </footer>
      <style>
        .site-footer {
          background-color: #1f2937;
          color: #9ca3af;
          padding: 64px 24px 24px;
        }
        .footer-container {
          max-width: 1200px;
          margin: 0 auto;
        }
        .footer-main {
          display: grid;
          grid-template-columns: 2fr 1fr 1fr 1fr;
          gap: 48px;
          margin-bottom: 48px;
        }
        .footer-logo {
          font-size: 1.5rem;
          font-weight: 700;
          color: white;
          margin-bottom: 16px;
        }
        .footer-description {
          margin-bottom: 24px;
          line-height: 1.6;
        }
        .footer-social {
          display: flex;
          gap: 12px;
        }
        .social-link {
          width: 40px;
          height: 40px;
          background-color: #374151;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          text-decoration: none;
          font-size: 0.75rem;
          font-weight: 600;
          transition: background-color 0.2s;
        }
        .social-link:hover {
          background-color: #3b82f6;
        }
        .footer-links h4 {
          color: white;
          font-weight: 600;
          margin-bottom: 20px;
        }
        .footer-links ul {
          list-style: none;
          padding: 0;
          margin: 0;
        }
        .footer-links li {
          margin-bottom: 12px;
        }
        .footer-links a {
          color: #9ca3af;
          text-decoration: none;
          transition: color 0.2s;
        }
        .footer-links a:hover {
          color: white;
        }
        .footer-bottom {
          padding-top: 24px;
          border-top: 1px solid #374151;
          text-align: center;
        }
        @media (max-width: 768px) {
          .footer-main {
            grid-template-columns: 1fr 1fr;
          }
        }
        @media (max-width: 480px) {
          .footer-main {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC TÉMOIGNAGES
  // ===========================================
  blockManager.add('testimonials-block', {
    label: 'Témoignages',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="none" stroke="currentColor" stroke-width="1.5"/>
      <text x="6" y="12" font-size="8">"</text>
      <text x="15" y="16" font-size="8">"</text>
    </svg>`,
    content: `
      <section class="testimonials-section">
        <div class="testimonials-container">
          <h2 class="testimonials-title">Ce que disent nos clients</h2>
          <div class="testimonials-grid">
            <div class="testimonial-card">
              <div class="testimonial-quote">"Service exceptionnel ! L'équipe a su comprendre nos besoins et livrer un produit de qualité."</div>
              <div class="testimonial-author">
                <img src="https://placehold.co/60x60/3b82f6/ffffff?text=JD" alt="Avatar" class="testimonial-avatar"/>
                <div>
                  <div class="testimonial-name">Jean Dupont</div>
                  <div class="testimonial-role">CEO, Entreprise ABC</div>
                </div>
              </div>
            </div>
            <div class="testimonial-card">
              <div class="testimonial-quote">"Très professionnel et à l'écoute. Je recommande vivement leurs services."</div>
              <div class="testimonial-author">
                <img src="https://placehold.co/60x60/10b981/ffffff?text=MM" alt="Avatar" class="testimonial-avatar"/>
                <div>
                  <div class="testimonial-name">Marie Martin</div>
                  <div class="testimonial-role">Directrice Marketing</div>
                </div>
              </div>
            </div>
            <div class="testimonial-card">
              <div class="testimonial-quote">"Un partenaire de confiance pour tous nos projets digitaux. Résultats au rendez-vous !"</div>
              <div class="testimonial-author">
                <img src="https://placehold.co/60x60/f59e0b/ffffff?text=PL" alt="Avatar" class="testimonial-avatar"/>
                <div>
                  <div class="testimonial-name">Pierre Leblanc</div>
                  <div class="testimonial-role">Fondateur, Startup XYZ</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <style>
        .testimonials-section {
          padding: 80px 24px;
          background-color: #f8fafc;
        }
        .testimonials-container {
          max-width: 1200px;
          margin: 0 auto;
        }
        .testimonials-title {
          text-align: center;
          font-size: 2.25rem;
          font-weight: 700;
          color: #1f2937;
          margin-bottom: 48px;
        }
        .testimonials-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 32px;
        }
        .testimonial-card {
          background-color: white;
          padding: 32px;
          border-radius: 12px;
          box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .testimonial-quote {
          font-size: 1.125rem;
          color: #4b5563;
          line-height: 1.8;
          margin-bottom: 24px;
          font-style: italic;
        }
        .testimonial-author {
          display: flex;
          align-items: center;
          gap: 16px;
        }
        .testimonial-avatar {
          width: 60px;
          height: 60px;
          border-radius: 50%;
        }
        .testimonial-name {
          font-weight: 600;
          color: #1f2937;
        }
        .testimonial-role {
          color: #6b7280;
          font-size: 0.875rem;
        }
        @media (max-width: 768px) {
          .testimonials-grid {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC CTA (Call To Action)
  // ===========================================
  blockManager.add('cta-block', {
    label: 'Call To Action',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="2" y="6" width="20" height="12" rx="2" fill="currentColor" opacity="0.2"/>
      <rect x="8" y="10" width="8" height="4" rx="1" fill="currentColor"/>
    </svg>`,
    content: `
      <section class="cta-section">
        <div class="cta-container">
          <h2 class="cta-title">Prêt à commencer ?</h2>
          <p class="cta-text">Rejoignez des milliers de clients satisfaits et transformez votre business dès aujourd'hui.</p>
          <div class="cta-buttons">
            <a href="#" class="cta-btn-primary">Démarrer maintenant</a>
            <a href="#" class="cta-btn-secondary">Nous contacter</a>
          </div>
        </div>
      </section>
      <style>
        .cta-section {
          padding: 80px 24px;
          background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .cta-container {
          max-width: 800px;
          margin: 0 auto;
          text-align: center;
        }
        .cta-title {
          font-size: 2.5rem;
          font-weight: 700;
          color: white;
          margin-bottom: 16px;
        }
        .cta-text {
          font-size: 1.25rem;
          color: rgba(255,255,255,0.9);
          margin-bottom: 32px;
        }
        .cta-buttons {
          display: flex;
          justify-content: center;
          gap: 16px;
        }
        .cta-btn-primary {
          background-color: white;
          color: #3b82f6;
          padding: 14px 32px;
          border-radius: 8px;
          font-weight: 600;
          text-decoration: none;
          transition: transform 0.2s;
        }
        .cta-btn-primary:hover {
          transform: translateY(-2px);
        }
        .cta-btn-secondary {
          background-color: transparent;
          color: white;
          padding: 14px 32px;
          border-radius: 8px;
          font-weight: 600;
          text-decoration: none;
          border: 2px solid rgba(255,255,255,0.3);
          transition: border-color 0.2s;
        }
        .cta-btn-secondary:hover {
          border-color: white;
        }
        @media (max-width: 480px) {
          .cta-buttons {
            flex-direction: column;
          }
          .cta-title {
            font-size: 1.75rem;
          }
        }
      </style>
    `
  });

  // ===========================================
  // BLOC FEATURES (Fonctionnalités)
  // ===========================================
  blockManager.add('features-block', {
    label: 'Fonctionnalités',
    category: categoryLabel,
    media: `<svg viewBox="0 0 24 24" fill="currentColor">
      <rect x="3" y="3" width="7" height="7" rx="1.5"/>
      <rect x="14" y="3" width="7" height="7" rx="1.5"/>
      <rect x="3" y="14" width="7" height="7" rx="1.5"/>
      <rect x="14" y="14" width="7" height="7" rx="1.5"/>
    </svg>`,
    content: `
      <section class="features-section">
        <div class="features-container">
          <div class="features-header">
            <h2 class="features-title">Nos services</h2>
            <p class="features-subtitle">Découvrez ce que nous pouvons faire pour vous</p>
          </div>
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">🚀</div>
              <h3 class="feature-title">Performance</h3>
              <p class="feature-text">Sites rapides et optimisés pour la meilleure expérience utilisateur.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🎨</div>
              <h3 class="feature-title">Design moderne</h3>
              <p class="feature-text">Interfaces élégantes et intuitives adaptées à votre marque.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">📱</div>
              <h3 class="feature-title">Responsive</h3>
              <p class="feature-text">Parfaitement adapté à tous les appareils et tailles d'écran.</p>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🔒</div>
              <h3 class="feature-title">Sécurité</h3>
              <p class="feature-text">Protection optimale de vos données et de vos utilisateurs.</p>
            </div>
          </div>
        </div>
      </section>
      <style>
        .features-section {
          padding: 80px 24px;
          background-color: #ffffff;
        }
        .features-container {
          max-width: 1200px;
          margin: 0 auto;
        }
        .features-header {
          text-align: center;
          margin-bottom: 48px;
        }
        .features-title {
          font-size: 2.25rem;
          font-weight: 700;
          color: #1f2937;
          margin-bottom: 12px;
        }
        .features-subtitle {
          color: #6b7280;
          font-size: 1.125rem;
        }
        .features-grid {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 32px;
        }
        .feature-card {
          text-align: center;
          padding: 32px 24px;
          border-radius: 12px;
          transition: box-shadow 0.3s;
        }
        .feature-card:hover {
          box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .feature-icon {
          font-size: 3rem;
          margin-bottom: 20px;
        }
        .feature-title {
          font-size: 1.25rem;
          font-weight: 600;
          color: #1f2937;
          margin-bottom: 12px;
        }
        .feature-text {
          color: #6b7280;
          line-height: 1.6;
        }
        @media (max-width: 992px) {
          .features-grid {
            grid-template-columns: repeat(2, 1fr);
          }
        }
        @media (max-width: 480px) {
          .features-grid {
            grid-template-columns: 1fr;
          }
        }
      </style>
    `
  });

  console.log('✅ Blocs personnalisés enregistrés');
}
