/**
 * ===========================================
 * Contrôleur des pages
 * ===========================================
 * 
 * Gère les opérations CRUD sur les pages d'un projet.
 * Chaque page stocke ses propres données GrapesJS.
 */

const { Page, Project } = require('../models');
const logger = require('../config/logger');
const { ApiError, asyncHandler } = require('../middleware/errorHandler');

/**
 * GET /api/projects/:projectId/pages
 * Liste toutes les pages d'un projet
 */
const listPages = asyncHandler(async (req, res) => {
  const { projectId } = req.params;

  const pages = await Page.getByProject(projectId);

  res.json({
    success: true,
    data: { pages }
  });
});

/**
 * POST /api/projects/:projectId/pages
 * Crée une nouvelle page dans un projet
 * 
 * Body attendu:
 * - name: Nom de la page (requis)
 * - slug: Slug URL (optionnel, généré depuis le nom si absent)
 * - grapesjs_data: Données GrapesJS initiales (optionnel)
 * - is_homepage: Si c'est la page d'accueil (optionnel)
 */
const createPage = asyncHandler(async (req, res) => {
  const { projectId } = req.params;
  const { name, slug, grapesjs_data, is_homepage, seo } = req.body;

  if (!name) {
    throw new ApiError(400, 'Le nom de la page est requis');
  }

  // Générer le slug si non fourni
  const pageSlug = slug || name
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // Supprimer les accents
    .replace(/[^a-z0-9]+/g, '-')     // Remplacer les caractères spéciaux par des tirets
    .replace(/^-|-$/g, '');          // Supprimer les tirets en début/fin

  // Vérifier l'unicité du slug dans le projet
  const existingPage = await Page.findOne({
    where: {
      project_id: projectId,
      slug: pageSlug
    }
  });

  if (existingPage) {
    throw new ApiError(409, `Une page avec le slug "${pageSlug}" existe déjà`);
  }

  // Déterminer l'ordre de la nouvelle page
  const lastPage = await Page.findOne({
    where: { project_id: projectId },
    order: [['order_index', 'DESC']]
  });
  const orderIndex = lastPage ? lastPage.order_index + 1 : 0;

  // Créer la page
  const page = await Page.create({
    project_id: projectId,
    name,
    slug: pageSlug,
    grapesjs_data: grapesjs_data || Page.getDefaultPageData(),
    is_homepage: is_homepage || false,
    order_index: orderIndex,
    seo: seo || {}
  });

  logger.info(`Page créée: ${page.id} (${name}) dans projet ${projectId}`);

  res.status(201).json({
    success: true,
    message: 'Page créée avec succès',
    data: { page }
  });
});

/**
 * GET /api/projects/:projectId/pages/:pageId
 * Récupère une page spécifique avec ses données GrapesJS
 */
const getPage = asyncHandler(async (req, res) => {
  const { projectId, pageId } = req.params;

  const page = await Page.findOne({
    where: {
      id: pageId,
      project_id: projectId
    }
  });

  if (!page) {
    throw new ApiError(404, 'Page non trouvée');
  }

  res.json({
    success: true,
    data: { page }
  });
});

/**
 * PUT /api/projects/:projectId/pages/:pageId
 * Met à jour une page (données GrapesJS, nom, slug, etc.)
 * 
 * Body attendu (tous optionnels):
 * - name: Nouveau nom
 * - slug: Nouveau slug
 * - grapesjs_data: Nouvelles données GrapesJS
 * - is_homepage: Définir comme page d'accueil
 * - seo: Métadonnées SEO
 * - order_index: Nouvel ordre
 */
const updatePage = asyncHandler(async (req, res) => {
  const { projectId, pageId } = req.params;
  const { name, slug, grapesjs_data, is_homepage, seo, order_index } = req.body;

  const page = await Page.findOne({
    where: {
      id: pageId,
      project_id: projectId
    }
  });

  if (!page) {
    throw new ApiError(404, 'Page non trouvée');
  }

  // Si changement de slug, vérifier l'unicité
  if (slug && slug !== page.slug) {
    const existingPage = await Page.findOne({
      where: {
        project_id: projectId,
        slug: slug
      }
    });

    if (existingPage) {
      throw new ApiError(409, `Une page avec le slug "${slug}" existe déjà`);
    }
    page.slug = slug;
  }

  // Mise à jour des champs
  if (name !== undefined) page.name = name;
  if (grapesjs_data !== undefined) page.grapesjs_data = grapesjs_data;
  if (is_homepage !== undefined) page.is_homepage = is_homepage;
  if (order_index !== undefined) page.order_index = order_index;
  
  if (seo !== undefined) {
    page.seo = { ...page.seo, ...seo };
  }

  await page.save();

  res.json({
    success: true,
    message: 'Page mise à jour',
    data: { page }
  });
});

/**
 * DELETE /api/projects/:projectId/pages/:pageId
 * Supprime une page
 */
const deletePage = asyncHandler(async (req, res) => {
  const { projectId, pageId } = req.params;

  const page = await Page.findOne({
    where: {
      id: pageId,
      project_id: projectId
    }
  });

  if (!page) {
    throw new ApiError(404, 'Page non trouvée');
  }

  // Empêcher la suppression de la dernière page
  const pageCount = await Page.count({
    where: { project_id: projectId }
  });

  if (pageCount <= 1) {
    throw new ApiError(400, 'Impossible de supprimer la dernière page du projet');
  }

  // Si c'était la homepage, promouvoir une autre page
  if (page.is_homepage) {
    const nextPage = await Page.findOne({
      where: {
        project_id: projectId,
        id: { [require('sequelize').Op.ne]: pageId }
      },
      order: [['order_index', 'ASC']]
    });

    if (nextPage) {
      nextPage.is_homepage = true;
      await nextPage.save();
    }
  }

  await page.destroy();

  logger.info(`Page supprimée: ${pageId} du projet ${projectId}`);

  res.json({
    success: true,
    message: 'Page supprimée avec succès'
  });
});

/**
 * PUT /api/projects/:projectId/pages/reorder
 * Réordonne les pages d'un projet
 * 
 * Body attendu:
 * - pages: Array de { id, order_index }
 */
const reorderPages = asyncHandler(async (req, res) => {
  const { projectId } = req.params;
  const { pages } = req.body;

  if (!Array.isArray(pages)) {
    throw new ApiError(400, 'Le paramètre "pages" doit être un tableau');
  }

  // Mettre à jour l'ordre de chaque page
  await Promise.all(
    pages.map(({ id, order_index }) =>
      Page.update(
        { order_index },
        { where: { id, project_id: projectId } }
      )
    )
  );

  // Récupérer les pages mises à jour
  const updatedPages = await Page.getByProject(projectId);

  res.json({
    success: true,
    message: 'Ordre des pages mis à jour',
    data: { pages: updatedPages }
  });
});

/**
 * POST /api/projects/:projectId/pages/:pageId/duplicate
 * Duplique une page
 */
const duplicatePage = asyncHandler(async (req, res) => {
  const { projectId, pageId } = req.params;

  const originalPage = await Page.findOne({
    where: {
      id: pageId,
      project_id: projectId
    }
  });

  if (!originalPage) {
    throw new ApiError(404, 'Page non trouvée');
  }

  // Générer un nouveau slug unique
  let newSlug = `${originalPage.slug}-copy`;
  let counter = 1;
  
  while (await Page.findOne({ where: { project_id: projectId, slug: newSlug } })) {
    newSlug = `${originalPage.slug}-copy-${counter}`;
    counter++;
  }

  // Déterminer l'ordre
  const lastPage = await Page.findOne({
    where: { project_id: projectId },
    order: [['order_index', 'DESC']]
  });

  // Créer la copie
  const newPage = await Page.create({
    project_id: projectId,
    name: `${originalPage.name} (copie)`,
    slug: newSlug,
    grapesjs_data: originalPage.grapesjs_data,
    is_homepage: false, // La copie n'est jamais la homepage
    order_index: lastPage ? lastPage.order_index + 1 : 0,
    seo: originalPage.seo
  });

  res.status(201).json({
    success: true,
    message: 'Page dupliquée avec succès',
    data: { page: newPage }
  });
});

module.exports = {
  listPages,
  createPage,
  getPage,
  updatePage,
  deletePage,
  reorderPages,
  duplicatePage
};
