/**
 * ===========================================
 * Service API - Communication avec le backend
 * ===========================================
 * 
 * Centralise tous les appels API vers le backend.
 * Gère l'authentification et les erreurs.
 */

import axios from 'axios';

// URL de base de l'API
const API_BASE_URL = import.meta.env.VITE_API_URL || '/builder/api';

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
 */
export function getStoredToken() {
  return localStorage.getItem('sitebuilder_token');
}

// Intercepteur pour gérer les erreurs globalement
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('sitebuilder_token');
      setAuthToken(null);
      
      if (!window.location.pathname.includes('/auth')) {
        window.location.href = '/builder/error?message=Session expirée';
      }
    }
    
    return Promise.reject(error);
  }
);

// ===========================================
// API AUTHENTIFICATION
// ===========================================

export const authApi = {
  ssoLogin: (params) => api.post('/auth/sso', params),
  getCurrentUser: () => api.get('/auth/me'),
  logout: () => api.post('/auth/logout'),
  refreshToken: () => api.post('/auth/refresh')
};

// ===========================================
// API PROJETS
// ===========================================

export const projectApi = {
  list: () => api.get('/projects'),
  create: (data) => api.post('/projects', data),
  get: (projectId) => api.get(`/projects/${projectId}`),
  update: (projectId, data) => api.put(`/projects/${projectId}`, data),
  delete: (projectId) => api.delete(`/projects/${projectId}`),
  publish: (projectId) => api.post(`/projects/${projectId}/publish`),
  preview: (projectId, pageSlug = 'index') => 
    api.get(`/projects/${projectId}/preview?page=${pageSlug}`)
};

// ===========================================
// API PAGES
// ===========================================

export const pageApi = {
  list: (projectId) => api.get(`/projects/${projectId}/pages`),
  create: (projectId, data) => api.post(`/projects/${projectId}/pages`, data),
  get: (projectId, pageId) => api.get(`/projects/${projectId}/pages/${pageId}`),
  update: (projectId, pageId, data) => 
    api.put(`/projects/${projectId}/pages/${pageId}`, data),
  delete: (projectId, pageId) => 
    api.delete(`/projects/${projectId}/pages/${pageId}`),
  reorder: (projectId, pages) => 
    api.put(`/projects/${projectId}/pages/reorder`, { pages }),
  duplicate: (projectId, pageId) => 
    api.post(`/projects/${projectId}/pages/${pageId}/duplicate`)
};

// ===========================================
// API ASSETS (Media Manager)
// ===========================================

export const assetApi = {
  /**
   * Liste les assets d'un projet
   */
  list: (projectId, params = {}) => 
    api.get(`/projects/${projectId}/assets`, { params }),
  
  /**
   * Upload de fichiers
   */
  upload: (projectId, formData) => 
    api.post(`/projects/${projectId}/assets`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
  
  /**
   * Met à jour un asset
   */
  update: (projectId, assetId, data) => 
    api.put(`/projects/${projectId}/assets/${assetId}`, data),
  
  /**
   * Supprime un asset
   */
  delete: (projectId, assetId) => 
    api.delete(`/projects/${projectId}/assets/${assetId}`),
  
  /**
   * Liste les dossiers
   */
  folders: (projectId) => 
    api.get(`/projects/${projectId}/assets/folders`)
};

// ===========================================
// API FORMULAIRES
// ===========================================

export const formApi = {
  /**
   * Soumet un formulaire (endpoint public)
   */
  submit: (data) => api.post('/forms/submit', data),
  
  /**
   * Liste les soumissions d'un projet
   */
  listSubmissions: (projectId, params = {}) => 
    api.get(`/projects/${projectId}/forms/submissions`, { params }),
  
  /**
   * Récupère une soumission
   */
  getSubmission: (projectId, submissionId) => 
    api.get(`/projects/${projectId}/forms/submissions/${submissionId}`),
  
  /**
   * Met à jour une soumission
   */
  updateSubmission: (projectId, submissionId, data) => 
    api.put(`/projects/${projectId}/forms/submissions/${submissionId}`, data),
  
  /**
   * Supprime une soumission
   */
  deleteSubmission: (projectId, submissionId) => 
    api.delete(`/projects/${projectId}/forms/submissions/${submissionId}`),
  
  /**
   * Compte les nouvelles soumissions
   */
  countNew: (projectId) => 
    api.get(`/projects/${projectId}/forms/count`)
};

export default api;
