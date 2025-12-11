/**
 * ===========================================
 * Service de publication des sites
 * ===========================================
 * 
 * Génère les fichiers statiques (HTML, CSS, JS) à partir
 * des données GrapesJS et les écrit dans le dossier cible.
 * 
 * STRATÉGIE DE PERMISSIONS LINUX:
 * 
 * Option 1 (Recommandée): Groupe partagé
 * - Créer un groupe "sitebuilder" 
 * - Ajouter l'utilisateur Node.js et www-data à ce groupe
 * - Configurer les dossiers web avec ce groupe et permissions 775
 * 
 * Option 2: ACL
 * - Utiliser setfacl pour donner les droits au process Node
 * - setfacl -R -m u:nodeuser:rwx /home/*/web/*/public_html
 * 
 * Option 3: Script sudo (moins sécurisé)
 * - Créer un script d'écriture avec sudo limité
 */

const fs = require('fs').promises;
const path = require('path');
const config = require('../config');
const logger = require('../config/logger');

/**
 * Publie un projet en générant tous les fichiers statiques
 * 
 * @param {Project} project - Instance du projet
 * @param {Page[]} pages - Liste des pages du projet
 * @param {User} user - Utilisateur propriétaire
 * @returns {object} Résultat de la publication
 */
async function publishProject(project, pages, user) {
  const publishPath = project.getPublishPath(
    config.hestia.publishBasePath,
    user.hestia_username
  );

  logger.info(`Publication vers: ${publishPath}`);

  // Vérifier et créer le dossier de destination
  await ensureDirectory(publishPath);

  const publishedFiles = [];

  // Générer le CSS global
  const cssContent = generateGlobalCss(project.settings);
  const cssPath = path.join(publishPath, 'style.css');
  await writeFile(cssPath, cssContent);
  publishedFiles.push('style.css');

  // Générer le JS global (si nécessaire)
  const jsContent = generateGlobalJs();
  const jsPath = path.join(publishPath, 'app.js');
  await writeFile(jsPath, jsContent);
  publishedFiles.push('app.js');

  // Générer chaque page HTML
  for (const page of pages) {
    const html = generatePageHtml(page, project.settings, pages);
    const fileName = page.getFileName();
    const filePath = path.join(publishPath, fileName);
    
    await writeFile(filePath, html);
    publishedFiles.push(fileName);
    
    logger.debug(`Page générée: ${fileName}`);
  }

  // Copier les assets si nécessaire
  // TODO: Implémenter la gestion des assets (images uploadées)

  logger.info(`Publication terminée: ${publishedFiles.length} fichiers`);

  return {
    publishPath,
    files: publishedFiles,
    timestamp: new Date().toISOString()
  };
}

/**
 * Génère le HTML complet d'une page
 * 
 * @param {Page} page - Instance de la page
 * @param {object} projectSettings - Paramètres globaux du projet
 * @param {Page[]} allPages - Toutes les pages (pour la navigation)
 * @returns {string} HTML complet de la page
 */
function generatePageHtml(page, projectSettings, allPages = []) {
  const { grapesjs_data, seo, name } = page;
  const { colors, fonts, seo: projectSeo } = projectSettings || {};

  // Extraction des composants et styles GrapesJS
  const components = grapesjs_data?.components || [];
  const styles = grapesjs_data?.styles || [];

  // Générer le HTML des composants
  const bodyContent = generateComponentsHtml(components);

  // Générer le CSS inline des styles GrapesJS
  const inlineStyles = generateInlineStyles(styles);

  // Méta SEO (priorité: page > projet)
  const pageTitle = seo?.title || projectSeo?.title || name;
  const pageDescription = seo?.description || projectSeo?.description || '';
  const pageKeywords = seo?.keywords || projectSeo?.keywords || '';

  // Générer la navigation si plusieurs pages
  const navigation = allPages.length > 1 
    ? generateNavigation(allPages, page.slug)
    : '';

  return `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="${escapeHtml(pageDescription)}">
  <meta name="keywords" content="${escapeHtml(pageKeywords)}">
  
  <!-- Open Graph -->
  <meta property="og:title" content="${escapeHtml(pageTitle)}">
  <meta property="og:description" content="${escapeHtml(pageDescription)}">
  ${seo?.og_image ? `<meta property="og:image" content="${escapeHtml(seo.og_image)}">` : ''}
  
  <title>${escapeHtml(pageTitle)}</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Styles -->
  <link rel="stylesheet" href="style.css">
  
  <!-- Styles GrapesJS inline -->
  <style>
    ${inlineStyles}
  </style>
</head>
<body>
  ${navigation}
  
  <main id="content">
    ${bodyContent}
  </main>
  
  <!-- Scripts -->
  <script src="app.js"></script>
</body>
</html>`;
}

/**
 * Génère le HTML à partir des composants GrapesJS
 * 
 * @param {array} components - Composants GrapesJS
 * @returns {string} HTML généré
 */
function generateComponentsHtml(components) {
  if (!Array.isArray(components) || components.length === 0) {
    return '<p>Page vide</p>';
  }

  return components.map(component => componentToHtml(component)).join('\n');
}

/**
 * Convertit un composant GrapesJS en HTML
 * Fonction récursive pour gérer les composants imbriqués
 * 
 * @param {object} component - Composant GrapesJS
 * @returns {string} HTML du composant
 */
function componentToHtml(component) {
  if (!component) return '';

  // Composant texte simple
  if (component.type === 'text' || component.type === 'textnode') {
    return component.content || '';
  }

  // Déterminer le tag HTML
  const tagName = component.tagName || getTagForType(component.type) || 'div';
  
  // Construire les attributs
  const attributes = buildAttributes(component);
  
  // Contenu: soit du texte, soit des composants enfants
  let content = '';
  
  if (component.content) {
    content = component.content;
  }
  
  if (component.components && Array.isArray(component.components)) {
    content += component.components.map(c => componentToHtml(c)).join('');
  }

  // Tags auto-fermants
  const selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link'];
  if (selfClosingTags.includes(tagName)) {
    return `<${tagName}${attributes}>`;
  }

  return `<${tagName}${attributes}>${content}</${tagName}>`;
}

/**
 * Construit la chaîne d'attributs HTML
 */
function buildAttributes(component) {
  const attrs = [];

  // Classes CSS
  if (component.classes && Array.isArray(component.classes)) {
    const classNames = component.classes
      .map(c => typeof c === 'string' ? c : c.name)
      .filter(Boolean)
      .join(' ');
    if (classNames) {
      attrs.push(`class="${escapeHtml(classNames)}"`);
    }
  }

  // ID
  if (component.attributes?.id) {
    attrs.push(`id="${escapeHtml(component.attributes.id)}"`);
  }

  // Autres attributs
  if (component.attributes) {
    for (const [key, value] of Object.entries(component.attributes)) {
      if (key !== 'id' && key !== 'class' && value !== undefined) {
        attrs.push(`${escapeHtml(key)}="${escapeHtml(String(value))}"`);
      }
    }
  }

  // Attributs src, href, etc.
  if (component.src) attrs.push(`src="${escapeHtml(component.src)}"`);
  if (component.href) attrs.push(`href="${escapeHtml(component.href)}"`);
  if (component.alt) attrs.push(`alt="${escapeHtml(component.alt)}"`);

  return attrs.length > 0 ? ' ' + attrs.join(' ') : '';
}

/**
 * Retourne le tag HTML approprié pour un type GrapesJS
 */
function getTagForType(type) {
  const typeToTag = {
    'text': 'span',
    'link': 'a',
    'image': 'img',
    'video': 'video',
    'map': 'iframe',
    'link-block': 'a',
    'quote': 'blockquote',
    'section': 'section',
    'container': 'div',
    'row': 'div',
    'cell': 'div',
    'wrapper': 'div',
    'default': 'div'
  };

  return typeToTag[type] || 'div';
}

/**
 * Génère le CSS inline à partir des styles GrapesJS
 */
function generateInlineStyles(styles) {
  if (!Array.isArray(styles) || styles.length === 0) {
    return '';
  }

  return styles.map(styleRule => {
    const selectors = styleRule.selectors || [];
    const style = styleRule.style || {};

    // Construire le sélecteur CSS
    const selectorStr = selectors
      .map(s => typeof s === 'string' ? `.${s}` : `.${s.name}`)
      .join('');

    if (!selectorStr || Object.keys(style).length === 0) {
      return '';
    }

    // Construire les déclarations CSS
    const declarations = Object.entries(style)
      .map(([prop, value]) => `  ${prop}: ${value};`)
      .join('\n');

    return `${selectorStr} {\n${declarations}\n}`;
  }).filter(Boolean).join('\n\n');
}

/**
 * Génère le CSS global du site
 */
function generateGlobalCss(settings = {}) {
  const { colors = {}, fonts = {} } = settings;

  return `/**
 * Styles générés par HestiaCP Site Builder
 * Généré le: ${new Date().toISOString()}
 */

/* Variables CSS */
:root {
  --color-primary: ${colors.primary || '#3b82f6'};
  --color-secondary: ${colors.secondary || '#64748b'};
  --color-accent: ${colors.accent || '#f59e0b'};
  --color-background: ${colors.background || '#ffffff'};
  --color-text: ${colors.text || '#1f2937'};
  
  --font-heading: ${fonts.heading || 'Inter, sans-serif'};
  --font-body: ${fonts.body || 'Inter, sans-serif'};
  
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 2rem;
  --spacing-xl: 4rem;
}

/* Reset de base */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html {
  font-size: 16px;
  scroll-behavior: smooth;
}

body {
  font-family: var(--font-body);
  color: var(--color-text);
  background-color: var(--color-background);
  line-height: 1.6;
}

/* Typographie */
h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-heading);
  font-weight: 600;
  line-height: 1.2;
  margin-bottom: var(--spacing-md);
}

h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
h5 { font-size: 1.25rem; }
h6 { font-size: 1rem; }

p {
  margin-bottom: var(--spacing-md);
}

a {
  color: var(--color-primary);
  text-decoration: none;
  transition: color 0.2s ease;
}

a:hover {
  color: var(--color-accent);
}

img {
  max-width: 100%;
  height: auto;
}

/* Conteneurs */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--spacing-md);
}

section {
  padding: var(--spacing-xl) var(--spacing-md);
}

/* Boutons */
.btn, button {
  display: inline-block;
  padding: var(--spacing-sm) var(--spacing-lg);
  font-size: 1rem;
  font-weight: 500;
  text-align: center;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: var(--color-primary);
  color: white;
}

.btn-primary:hover {
  background-color: color-mix(in srgb, var(--color-primary) 85%, black);
}

.btn-secondary {
  background-color: var(--color-secondary);
  color: white;
}

/* Navigation */
.site-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md) var(--spacing-lg);
  background-color: var(--color-background);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.site-nav ul {
  display: flex;
  list-style: none;
  gap: var(--spacing-lg);
}

.site-nav a {
  font-weight: 500;
}

.site-nav a.active {
  color: var(--color-accent);
}

/* Utilitaires */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.mt-1 { margin-top: var(--spacing-xs); }
.mt-2 { margin-top: var(--spacing-sm); }
.mt-3 { margin-top: var(--spacing-md); }
.mt-4 { margin-top: var(--spacing-lg); }

.mb-1 { margin-bottom: var(--spacing-xs); }
.mb-2 { margin-bottom: var(--spacing-sm); }
.mb-3 { margin-bottom: var(--spacing-md); }
.mb-4 { margin-bottom: var(--spacing-lg); }

.py-1 { padding-top: var(--spacing-xs); padding-bottom: var(--spacing-xs); }
.py-2 { padding-top: var(--spacing-sm); padding-bottom: var(--spacing-sm); }
.py-3 { padding-top: var(--spacing-md); padding-bottom: var(--spacing-md); }
.py-4 { padding-top: var(--spacing-lg); padding-bottom: var(--spacing-lg); }

/* Responsive */
@media (max-width: 768px) {
  h1 { font-size: 2rem; }
  h2 { font-size: 1.75rem; }
  h3 { font-size: 1.5rem; }
  
  .site-nav {
    flex-direction: column;
    gap: var(--spacing-md);
  }
  
  .site-nav ul {
    flex-wrap: wrap;
    justify-content: center;
  }
}
`;
}

/**
 * Génère le JavaScript global du site
 */
function generateGlobalJs() {
  return `/**
 * Scripts générés par HestiaCP Site Builder
 * Généré le: ${new Date().toISOString()}
 */

document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll pour les ancres
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // Gestion du formulaire de contact (si présent)
  const contactForm = document.querySelector('.contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      // TODO: Implémenter l'envoi du formulaire
      alert('Merci pour votre message ! (Formulaire à configurer)');
    });
  }

  // Mobile menu toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const navMenu = document.querySelector('.nav-menu');
  if (menuToggle && navMenu) {
    menuToggle.addEventListener('click', function() {
      navMenu.classList.toggle('active');
    });
  }

  console.log('Site Builder - Site chargé avec succès');
});
`;
}

/**
 * Génère la navigation du site
 */
function generateNavigation(pages, currentSlug) {
  const links = pages.map(page => {
    const isActive = page.slug === currentSlug || 
      (page.is_homepage && currentSlug === 'index');
    const href = page.is_homepage ? 'index.html' : `${page.slug}.html`;
    const activeClass = isActive ? ' class="active"' : '';
    
    return `<li><a href="${href}"${activeClass}>${escapeHtml(page.name)}</a></li>`;
  }).join('\n        ');

  return `
  <nav class="site-nav">
    <div class="logo">Mon Site</div>
    <ul class="nav-menu">
      ${links}
    </ul>
  </nav>`;
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

/**
 * S'assure que le dossier existe
 */
async function ensureDirectory(dirPath) {
  try {
    await fs.access(dirPath);
  } catch {
    await fs.mkdir(dirPath, { recursive: true, mode: 0o755 });
    logger.debug(`Dossier créé: ${dirPath}`);
  }
}

/**
 * Écrit un fichier avec gestion des erreurs
 */
async function writeFile(filePath, content) {
  try {
    await fs.writeFile(filePath, content, { encoding: 'utf8', mode: 0o644 });
  } catch (error) {
    logger.error(`Erreur d'écriture: ${filePath} - ${error.message}`);
    throw new Error(`Impossible d'écrire le fichier ${filePath}: ${error.message}`);
  }
}

module.exports = {
  publishProject,
  generatePageHtml,
  generateGlobalCss,
  generateGlobalJs,
  generateComponentsHtml
};
