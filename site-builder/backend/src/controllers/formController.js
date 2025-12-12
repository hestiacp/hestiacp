/**
 * ===========================================
 * Contrôleur des Formulaires
 * ===========================================
 * 
 * Gère les soumissions de formulaires et leur administration.
 */

const { FormSubmission, Project, Page } = require('../models');
const logger = require('../config/logger');
const nodemailer = require('nodemailer');

// Configuration email (optionnel)
let transporter = null;
if (process.env.SMTP_HOST) {
  transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: process.env.SMTP_PORT || 587,
    secure: process.env.SMTP_SECURE === 'true',
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS
    }
  });
}

/**
 * Soumet un formulaire (endpoint public)
 * POST /api/forms/submit
 * 
 * Body:
 * - project_id: string (requis)
 * - form_id: string (optionnel, identifiant du formulaire)
 * - form_name: string (optionnel)
 * - page_slug: string (optionnel)
 * - data: object (les champs du formulaire)
 */
exports.submitForm = async (req, res, next) => {
  try {
    const { project_id, form_id, form_name, page_slug, data } = req.body;

    // Validation
    if (!project_id) {
      return res.status(400).json({
        success: false,
        message: 'project_id requis'
      });
    }

    if (!data || Object.keys(data).length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Aucune donnée de formulaire'
      });
    }

    // Vérifier que le projet existe
    const project = await Project.findByPk(project_id);
    if (!project) {
      return res.status(404).json({
        success: false,
        message: 'Projet non trouvé'
      });
    }

    // Trouver la page si spécifiée
    let page_id = null;
    if (page_slug) {
      const page = await Page.findOne({
        where: { project_id, slug: page_slug }
      });
      if (page) {
        page_id = page.id;
      }
    }

    // Collecter les infos visiteur
    const visitor_info = {
      ip: req.ip || req.connection.remoteAddress,
      user_agent: req.get('User-Agent'),
      referer: req.get('Referer'),
      timestamp: new Date().toISOString()
    };

    // Créer la soumission
    const submission = await FormSubmission.create({
      project_id,
      page_id,
      form_id: form_id || 'default',
      form_name: form_name || 'Contact Form',
      data,
      visitor_info,
      status: 'new'
    });

    // Envoyer notification email si configuré
    if (transporter && project.settings?.notification_email) {
      try {
        await sendNotificationEmail(project, submission, data);
        submission.notification_sent = true;
        await submission.save();
      } catch (emailError) {
        logger.error('Erreur envoi email:', emailError);
      }
    }

    // Webhook si configuré
    if (project.settings?.webhook_url) {
      try {
        await sendWebhook(project.settings.webhook_url, {
          event: 'form_submission',
          project_id,
          form_id,
          data,
          timestamp: new Date().toISOString()
        });
      } catch (webhookError) {
        logger.error('Erreur webhook:', webhookError);
      }
    }

    res.status(201).json({
      success: true,
      message: 'Formulaire soumis avec succès'
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Liste les soumissions d'un projet
 * GET /api/projects/:projectId/forms/submissions
 */
exports.listSubmissions = async (req, res, next) => {
  try {
    const { projectId } = req.params;
    const { form_id, status, limit = 50, offset = 0 } = req.query;

    const result = await FormSubmission.getByProject(projectId, {
      formId: form_id,
      status,
      limit: parseInt(limit),
      offset: parseInt(offset)
    });

    res.json({
      success: true,
      data: {
        submissions: result.rows,
        total: result.count,
        limit: parseInt(limit),
        offset: parseInt(offset)
      }
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Récupère une soumission spécifique
 * GET /api/projects/:projectId/forms/submissions/:submissionId
 */
exports.getSubmission = async (req, res, next) => {
  try {
    const { projectId, submissionId } = req.params;

    const submission = await FormSubmission.findOne({
      where: { id: submissionId, project_id: projectId },
      include: [{ model: Page, as: 'page', attributes: ['name', 'slug'] }]
    });

    if (!submission) {
      return res.status(404).json({
        success: false,
        message: 'Soumission non trouvée'
      });
    }

    // Marquer comme lue si nouvelle
    if (submission.status === 'new') {
      submission.status = 'read';
      await submission.save();
    }

    res.json({
      success: true,
      data: { submission }
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Met à jour le statut d'une soumission
 * PUT /api/projects/:projectId/forms/submissions/:submissionId
 */
exports.updateSubmission = async (req, res, next) => {
  try {
    const { projectId, submissionId } = req.params;
    const { status, notes } = req.body;

    const submission = await FormSubmission.findOne({
      where: { id: submissionId, project_id: projectId }
    });

    if (!submission) {
      return res.status(404).json({
        success: false,
        message: 'Soumission non trouvée'
      });
    }

    if (status) submission.status = status;
    if (notes !== undefined) submission.notes = notes;

    await submission.save();

    res.json({
      success: true,
      message: 'Soumission mise à jour',
      data: { submission }
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Supprime une soumission
 * DELETE /api/projects/:projectId/forms/submissions/:submissionId
 */
exports.deleteSubmission = async (req, res, next) => {
  try {
    const { projectId, submissionId } = req.params;

    const submission = await FormSubmission.findOne({
      where: { id: submissionId, project_id: projectId }
    });

    if (!submission) {
      return res.status(404).json({
        success: false,
        message: 'Soumission non trouvée'
      });
    }

    await submission.destroy();

    res.json({
      success: true,
      message: 'Soumission supprimée'
    });

  } catch (error) {
    next(error);
  }
};

/**
 * Compte les nouvelles soumissions
 * GET /api/projects/:projectId/forms/count
 */
exports.countNew = async (req, res, next) => {
  try {
    const { projectId } = req.params;

    const count = await FormSubmission.countNew(projectId);

    res.json({
      success: true,
      data: { count }
    });

  } catch (error) {
    next(error);
  }
};

// ===========================================
// FONCTIONS UTILITAIRES
// ===========================================

/**
 * Envoie un email de notification
 */
async function sendNotificationEmail(project, submission, data) {
  const emailTo = project.settings.notification_email;
  
  // Formater les données du formulaire
  const dataLines = Object.entries(data)
    .map(([key, value]) => `<strong>${key}:</strong> ${value}`)
    .join('<br>');

  await transporter.sendMail({
    from: process.env.SMTP_FROM || 'noreply@sitebuilder.local',
    to: emailTo,
    subject: `Nouveau message - ${project.project_name || project.domain_name}`,
    html: `
      <h2>Nouvelle soumission de formulaire</h2>
      <p><strong>Site:</strong> ${project.domain_name}</p>
      <p><strong>Formulaire:</strong> ${submission.form_name}</p>
      <hr>
      <h3>Données reçues:</h3>
      <p>${dataLines}</p>
      <hr>
      <p><small>Reçu le ${new Date().toLocaleString('fr-FR')}</small></p>
    `
  });
}

/**
 * Envoie un webhook
 */
async function sendWebhook(url, payload) {
  const fetch = require('node-fetch');
  
  await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    timeout: 5000
  });
}
