/**
 * ===========================================
 * Service API - Communication avec le backend
 * ===========================================
 * 
 * Centralise tous les appels API vers le backend.
 * Gère l'authentification et les erreurs.
 */

import axios from 'axios';

// URL de base de l'API (en dev, proxifié par Vite)
const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

// Instance Axios configurée
export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Token d'authentification
let authToken = null;

/**
 * Définit le token d'authentification pour toutes les requêtes
 * @param {string} token - Token JWT
 */
export function setAuthToken(token) {
  authToken = token;
  
  if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common['Authorization'];
  }
}

/**
 * Récupère le token stocké dans localStorage
 * @returns {string|null} Token JWT ou null
 */
export function getStoredToken() {
  return localStorage.getItem('sitebuilder_token');
}

// Intercepteur pour gérer les erreurs globalement
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Erreur d'authentification
    if (error.response?.status === 401) {
      localStorage.removeItem('sitebuilder_token');
      setAuthToken(null);
      
      // Rediriger vers la page d'erreur si non sur la page d'auth
      if (!window.location.pathname.includes('/auth')) {
        window.location.href = '/error?message=Session expirée';
      }
    }
    
    return Promise.reject(error);
  }
);

// ===========================================
// API AUTHENTIFICATION
// ===========================================

export const authApi = {
  /**
   * Authentification SSO
   * @param {object} params - Paramètres SSO (h_user, h_domain, h_sig)
   */
  ssoLogin: (params) => api.post('/auth/sso', params),
  
  /**
   * Récupère l'utilisateur connecté et ses projets
   */
  getCurrentUser: () => api.get('/auth/me'),
  
  /**
   * Déconnexion
   */
  logout: () => api.post('/auth/logout'),
  
  /**
   * Rafraîchit le token JWT
   */
  refreshToken: () => api.post('/auth/refresh')
};

// ===========================================
// API PROJETS
// ===========================================

export const projectApi = {
  /**
   * Liste tous les projets de l'utilisateur
   */
  list: () => api.get('/projects'),
  
  /**
   * Crée un nouveau projet
   * @param {object} data - { domain_name, project_name?, settings? }
   */
  create: (data) => api.post('/projects', data),
  
  /**
   * Récupère un projet avec ses pages
   * @param {string} projectId - ID du projet
   */
  get: (projectId) => api.get(`/projects/${projectId}`),
  
  /**
   * Met à jour un projet
   * @param {string} projectId - ID du projet
   * @param {object} data - Données à mettre à jour
   */
  update: (projectId, data) => api.put(`/projects/${projectId}`, data),
  
  /**
   * Supprime un projet
   * @param {string} projectId - ID du projet
   */
  delete: (projectId) => api.delete(`/projects/${projectId}`),
  
  /**
   * Publie le site
   * @param {string} projectId - ID du projet
   */
  publish: (projectId) => api.post(`/projects/${projectId}/publish`),
  
  /**
   * Génère un aperçu HTML
   * @param {string} projectId - ID du projet
   * @param {string} pageSlug - Slug de la page (optionnel)
   */
  preview: (projectId, pageSlug = 'index') => 
    api.get(`/projects/${projectId}/preview?page=${pageSlug}`)
};

// ===========================================
// API PAGES
// ===========================================

export const pageApi = {
  /**
   * Liste les pages d'un projet
   * @param {string} projectId - ID du projet
   */
  list: (projectId) => api.get(`/projects/${projectId}/pages`),
  
  /**
   * Crée une nouvelle page
   * @param {string} projectId - ID du projet
   * @param {object} data - { name, slug?, grapesjs_data?, is_homepage? }
   */
  create: (projectId, data) => api.post(`/projects/${projectId}/pages`, data),
  
  /**
   * Récupère une page avec ses données GrapesJS
   * @param {string} projectId - ID du projet
   * @param {string} pageId - ID de la page
   */
  get: (projectId, pageId) => api.get(`/projects/${projectId}/pages/${pageId}`),
  
  /**
   * Met à jour une page (sauvegarde GrapesJS)
   * @param {string} projectId - ID du projet
   * @param {string} pageId - ID de la page
   * @param {object} data - Données à mettre à jour
   */
  update: (projectId, pageId, data) => 
    api.put(`/projects/${projectId}/pages/${pageId}`, data),
  
  /**
   * Supprime une page
   * @param {string} projectId - ID du projet
   * @param {string} pageId - ID de la page
   */
  delete: (projectId, pageId) => 
    api.delete(`/projects/${projectId}/pages/${pageId}`),
  
  /**
   * Réordonne les pages
   * @param {string} projectId - ID du projet
   * @param {array} pages - [{ id, order_index }]
   */
  reorder: (projectId, pages) => 
    api.put(`/projects/${projectId}/pages/reorder`, { pages }),
  
  /**
   * Duplique une page
   * @param {string} projectId - ID du projet
   * @param {string} pageId - ID de la page à dupliquer
   */
  duplicate: (projectId, pageId) => 
    api.post(`/projects/${projectId}/pages/${pageId}/duplicate`)
};

export default api;
