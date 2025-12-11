/**
 * ===========================================
 * Contrôleur des projets
 * ===========================================
 * 
 * Gère les opérations CRUD sur les projets
 * et la publication des sites.
 */

const { Project, Page, User } = require('../models');
const publishService = require('../services/publishService');
const logger = require('../config/logger');
const { ApiError, asyncHandler } = require('../middleware/errorHandler');

/**
 * GET /api/projects
 * Liste tous les projets de l'utilisateur connecté
 */
const listProjects = asyncHandler(async (req, res) => {
  const projects = await Project.findAll({
    where: { user_id: req.userId },
    include: [{
      association: 'pages',
      attributes: ['id', 'name', 'slug', 'is_homepage', 'order_index']
    }],
    order: [['updated_at', 'DESC']]
  });

  res.json({
    success: true,
    data: { projects }
  });
});

/**
 * POST /api/projects
 * Crée un nouveau projet
 * 
 * Body attendu:
 * - domain_name: Nom de domaine (requis)
 * - project_name: Nom du projet (optionnel)
 * - settings: Paramètres du projet (optionnel)
 */
const createProject = asyncHandler(async (req, res) => {
  const { domain_name, project_name, settings } = req.body;

  if (!domain_name) {
    throw new ApiError(400, 'Le nom de domaine est requis');
  }

  // Vérifier si un projet existe déjà pour ce domaine
  const existingProject = await Project.findOne({
    where: {
      user_id: req.userId,
      domain_name
    }
  });

  if (existingProject) {
    throw new ApiError(409, 'Un projet existe déjà pour ce domaine');
  }

  // Créer le projet avec une page d'accueil par défaut
  const { project } = await Project.findOrCreateByDomain(
    req.userId,
    domain_name,
    project_name
  );

  // Mettre à jour les settings si fournis
  if (settings) {
    project.settings = { ...project.settings, ...settings };
    await project.save();
  }

  // Récupérer le projet avec ses pages
  const fullProject = await project.toFullJSON();

  logger.info(`Projet créé: ${project.id} pour ${req.user.hestia_username}`);

  res.status(201).json({
    success: true,
    message: 'Projet créé avec succès',
    data: { project: fullProject }
  });
});

/**
 * GET /api/projects/:id
 * Récupère un projet avec toutes ses pages
 */
const getProject = asyncHandler(async (req, res) => {
  // Le projet est déjà chargé par le middleware authorizeProjectOwner
  const project = req.project;

  // Charger les pages
  const fullProject = await project.toFullJSON();

  res.json({
    success: true,
    data: { project: fullProject }
  });
});

/**
 * PUT /api/projects/:id
 * Met à jour un projet (settings, nom, etc.)
 * 
 * Body attendu:
 * - project_name: Nouveau nom (optionnel)
 * - settings: Nouveaux paramètres (optionnel)
 * - thumbnail: Aperçu du projet (optionnel)
 */
const updateProject = asyncHandler(async (req, res) => {
  const project = req.project;
  const { project_name, settings, thumbnail, publish_path } = req.body;

  // Mise à jour des champs modifiables
  if (project_name !== undefined) {
    project.project_name = project_name;
  }

  if (settings !== undefined) {
    // Fusion des settings existants avec les nouveaux
    project.settings = { ...project.settings, ...settings };
  }

  if (thumbnail !== undefined) {
    project.thumbnail = thumbnail;
  }

  if (publish_path !== undefined) {
    project.publish_path = publish_path;
  }

  await project.save();

  const fullProject = await project.toFullJSON();

  res.json({
    success: true,
    message: 'Projet mis à jour',
    data: { project: fullProject }
  });
});

/**
 * DELETE /api/projects/:id
 * Supprime un projet et toutes ses pages
 */
const deleteProject = asyncHandler(async (req, res) => {
  const project = req.project;
  const projectId = project.id;
  const domainName = project.domain_name;

  // Les pages seront supprimées automatiquement (CASCADE)
  await project.destroy();

  logger.info(`Projet supprimé: ${projectId} (${domainName})`);

  res.json({
    success: true,
    message: 'Projet supprimé avec succès'
  });
});

/**
 * POST /api/projects/:id/publish
 * Publie le site en générant les fichiers statiques
 */
const publishProject = asyncHandler(async (req, res) => {
  const project = req.project;
  const user = req.user;

  logger.info(`Publication du projet ${project.id} par ${user.hestia_username}`);

  try {
    // Récupérer toutes les pages du projet
    const pages = await Page.getByProject(project.id);

    if (pages.length === 0) {
      throw new ApiError(400, 'Le projet n\'a aucune page à publier');
    }

    // Publier le site
    const result = await publishService.publishProject(project, pages, user);

    // Marquer le projet comme publié
    await project.markAsPublished();

    res.json({
      success: true,
      message: 'Site publié avec succès',
      data: {
        publishedAt: project.last_published_at,
        publishPath: result.publishPath,
        files: result.files
      }
    });
  } catch (error) {
    logger.error(`Erreur de publication: ${error.message}`);
    throw new ApiError(500, `Erreur lors de la publication: ${error.message}`);
  }
});

/**
 * GET /api/projects/:id/preview
 * Génère un aperçu HTML du projet sans le publier
 */
const previewProject = asyncHandler(async (req, res) => {
  const project = req.project;
  const pageSlug = req.query.page || 'index';

  // Trouver la page demandée
  const page = await Page.findOne({
    where: {
      project_id: project.id,
      slug: pageSlug
    }
  });

  if (!page) {
    throw new ApiError(404, 'Page non trouvée');
  }

  // Générer le HTML de prévisualisation
  const html = publishService.generatePageHtml(page, project.settings);

  res.type('html').send(html);
});

module.exports = {
  listProjects,
  createProject,
  getProject,
  updateProject,
  deleteProject,
  publishProject,
  previewProject
};
