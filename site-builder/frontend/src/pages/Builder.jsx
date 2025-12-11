/**
 * ===========================================
 * Page principale du Builder
 * ===========================================
 * 
 * Intègre GrapesJS avec les panneaux de navigation,
 * la gestion des pages et les contrôles de publication.
 */

import { useState, useEffect, useContext, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { AuthContext } from '../App';
import { projectApi, pageApi } from '../services/api';

// Composants du builder
import BuilderHeader from '../components/Builder/BuilderHeader';
import PageManager from '../components/Builder/PageManager';
import GrapesEditor from '../components/Builder/GrapesEditor';
import Toast from '../components/common/Toast';
import Loading from '../components/common/Loading';

function Builder() {
  const { projectId } = useParams();
  const navigate = useNavigate();
  const { user, project: contextProject, setProject } = useContext(AuthContext);

  // État du projet et des pages
  const [project, setLocalProject] = useState(null);
  const [pages, setPages] = useState([]);
  const [currentPage, setCurrentPage] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [publishing, setPublishing] = useState(false);

  // État de l'interface
  const [activeTab, setActiveTab] = useState('blocks'); // 'blocks' | 'pages' | 'layers'
  const [toasts, setToasts] = useState([]);

  // Référence à l'éditeur GrapesJS
  const [editor, setEditor] = useState(null);

  /**
   * Affiche une notification toast
   */
  const showToast = useCallback((message, type = 'info') => {
    const id = Date.now();
    setToasts(prev => [...prev, { id, message, type }]);
    
    // Auto-suppression après 3 secondes
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 3000);
  }, []);

  /**
   * Charge le projet et ses pages
   */
  useEffect(() => {
    const loadProject = async () => {
      try {
        setLoading(true);
        
        // Utiliser l'ID du contexte si non fourni dans l'URL
        const id = projectId || contextProject?.id;
        
        if (!id) {
          navigate('/error?message=Aucun projet sélectionné');
          return;
        }

        const response = await projectApi.get(id);
        
        if (response.data.success) {
          const projectData = response.data.data.project;
          setLocalProject(projectData);
          setProject(projectData);
          setPages(projectData.pages || []);
          
          // Sélectionner la première page (homepage)
          if (projectData.pages?.length > 0) {
            const homePage = projectData.pages.find(p => p.is_homepage) || projectData.pages[0];
            setCurrentPage(homePage);
          }
        }
      } catch (error) {
        console.error('Erreur chargement projet:', error);
        showToast('Erreur lors du chargement du projet', 'error');
      } finally {
        setLoading(false);
      }
    };

    loadProject();
  }, [projectId, contextProject?.id, navigate, setProject, showToast]);

  /**
   * Sauvegarde la page courante
   */
  const handleSave = useCallback(async () => {
    if (!editor || !currentPage || !project) return;

    try {
      setSaving(true);

      // Récupérer les données GrapesJS
      const grapesjs_data = {
        components: editor.getComponents().map(c => c.toJSON()),
        styles: editor.getStyle().map(s => s.toJSON()),
        assets: editor.AssetManager.getAll().map(a => a.toJSON())
      };

      // Sauvegarder via l'API
      await pageApi.update(project.id, currentPage.id, { grapesjs_data });

      // Mettre à jour l'état local
      setPages(prev => prev.map(p => 
        p.id === currentPage.id ? { ...p, grapesjs_data } : p
      ));

      showToast('Page sauvegardée avec succès', 'success');
    } catch (error) {
      console.error('Erreur sauvegarde:', error);
      showToast('Erreur lors de la sauvegarde', 'error');
    } finally {
      setSaving(false);
    }
  }, [editor, currentPage, project, showToast]);

  /**
   * Publie le site
   */
  const handlePublish = useCallback(async () => {
    if (!project) return;

    try {
      setPublishing(true);

      // Sauvegarder d'abord la page courante
      await handleSave();

      // Publier
      const response = await projectApi.publish(project.id);

      if (response.data.success) {
        showToast('Site publié avec succès !', 'success');
        
        // Mettre à jour l'état du projet
        setLocalProject(prev => ({
          ...prev,
          is_published: true,
          last_published_at: new Date().toISOString()
        }));
      }
    } catch (error) {
      console.error('Erreur publication:', error);
      showToast(`Erreur: ${error.response?.data?.message || 'Publication échouée'}`, 'error');
    } finally {
      setPublishing(false);
    }
  }, [project, handleSave, showToast]);

  /**
   * Change la page courante
   */
  const handlePageChange = useCallback(async (page) => {
    if (page.id === currentPage?.id) return;

    try {
      // Sauvegarder la page courante avant de changer
      if (editor && currentPage) {
        await handleSave();
      }

      // Charger les données de la nouvelle page
      const response = await pageApi.get(project.id, page.id);
      
      if (response.data.success) {
        const pageData = response.data.data.page;
        setCurrentPage(pageData);

        // Mettre à jour GrapesJS avec les nouvelles données
        if (editor) {
          editor.setComponents(pageData.grapesjs_data?.components || []);
          editor.setStyle(pageData.grapesjs_data?.styles || []);
        }
      }
    } catch (error) {
      console.error('Erreur changement de page:', error);
      showToast('Erreur lors du chargement de la page', 'error');
    }
  }, [currentPage, editor, project, handleSave, showToast]);

  /**
   * Ajoute une nouvelle page
   */
  const handleAddPage = useCallback(async (name) => {
    if (!project) return;

    try {
      const response = await pageApi.create(project.id, { name });
      
      if (response.data.success) {
        const newPage = response.data.data.page;
        setPages(prev => [...prev, newPage]);
        showToast('Page créée avec succès', 'success');
        
        // Basculer vers la nouvelle page
        handlePageChange(newPage);
      }
    } catch (error) {
      console.error('Erreur création page:', error);
      showToast(`Erreur: ${error.response?.data?.message || 'Création échouée'}`, 'error');
    }
  }, [project, handlePageChange, showToast]);

  /**
   * Supprime une page
   */
  const handleDeletePage = useCallback(async (pageId) => {
    if (!project || pages.length <= 1) {
      showToast('Impossible de supprimer la dernière page', 'warning');
      return;
    }

    try {
      await pageApi.delete(project.id, pageId);
      
      // Mettre à jour la liste
      const updatedPages = pages.filter(p => p.id !== pageId);
      setPages(updatedPages);
      
      // Si on supprime la page courante, basculer vers une autre
      if (currentPage?.id === pageId) {
        handlePageChange(updatedPages[0]);
      }
      
      showToast('Page supprimée', 'success');
    } catch (error) {
      console.error('Erreur suppression:', error);
      showToast('Erreur lors de la suppression', 'error');
    }
  }, [project, pages, currentPage, handlePageChange, showToast]);

  /**
   * Duplique une page
   */
  const handleDuplicatePage = useCallback(async (pageId) => {
    if (!project) return;

    try {
      const response = await pageApi.duplicate(project.id, pageId);
      
      if (response.data.success) {
        const newPage = response.data.data.page;
        setPages(prev => [...prev, newPage]);
        showToast('Page dupliquée', 'success');
      }
    } catch (error) {
      console.error('Erreur duplication:', error);
      showToast('Erreur lors de la duplication', 'error');
    }
  }, [project, showToast]);

  // Affichage du chargement
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-editor-bg">
        <Loading message="Chargement du builder..." />
      </div>
    );
  }

  // Erreur si pas de projet
  if (!project) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-editor-bg text-white">
        <div className="text-center">
          <h2 className="text-xl font-bold mb-2">Projet non trouvé</h2>
          <p className="text-gray-400">Le projet demandé n'existe pas ou vous n'y avez pas accès.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="builder-container">
      {/* Header avec logo, nom du projet et boutons */}
      <BuilderHeader
        project={project}
        currentPage={currentPage}
        onSave={handleSave}
        onPublish={handlePublish}
        saving={saving}
        publishing={publishing}
        user={user}
      />

      <div className="builder-main">
        {/* Sidebar gauche - Pages et Blocs */}
        <div className="builder-sidebar">
          <div className="sidebar-tabs">
            <button
              className={`sidebar-tab ${activeTab === 'blocks' ? 'active' : ''}`}
              onClick={() => setActiveTab('blocks')}
            >
              Blocs
            </button>
            <button
              className={`sidebar-tab ${activeTab === 'pages' ? 'active' : ''}`}
              onClick={() => setActiveTab('pages')}
            >
              Pages
            </button>
            <button
              className={`sidebar-tab ${activeTab === 'layers' ? 'active' : ''}`}
              onClick={() => setActiveTab('layers')}
            >
              Couches
            </button>
          </div>

          <div className="sidebar-content">
            {activeTab === 'pages' && (
              <PageManager
                pages={pages}
                currentPage={currentPage}
                onPageSelect={handlePageChange}
                onAddPage={handleAddPage}
                onDeletePage={handleDeletePage}
                onDuplicatePage={handleDuplicatePage}
              />
            )}
            
            {/* Les blocs et couches sont gérés par GrapesJS */}
            {activeTab === 'blocks' && (
              <div id="blocks-container" className="p-2"></div>
            )}
            
            {activeTab === 'layers' && (
              <div id="layers-container" className="p-2"></div>
            )}
          </div>
        </div>

        {/* Zone d'édition GrapesJS */}
        <div className="editor-canvas">
          <GrapesEditor
            currentPage={currentPage}
            projectSettings={project.settings}
            onEditorReady={setEditor}
          />
        </div>

        {/* Panneau droit - Styles */}
        <div className="builder-panel-right">
          <div id="styles-container"></div>
          <div id="traits-container"></div>
          <div id="selectors-container"></div>
        </div>
      </div>

      {/* Notifications Toast */}
      <div className="toast-container">
        {toasts.map(toast => (
          <Toast
            key={toast.id}
            message={toast.message}
            type={toast.type}
            onClose={() => setToasts(prev => prev.filter(t => t.id !== toast.id))}
          />
        ))}
      </div>
    </div>
  );
}

export default Builder;
