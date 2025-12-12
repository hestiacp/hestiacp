/**
 * ===========================================
 * Page principale du Builder - Version corrigée
 * ===========================================
 */

import { useState, useEffect, useContext, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { AuthContext } from '../App';
import { projectApi, pageApi } from '../services/api';

import BuilderHeader from '../components/Builder/BuilderHeader';
import PageManager from '../components/Builder/PageManager';
import GrapesEditor from '../components/Builder/GrapesEditor';
import Toast from '../components/common/Toast';
import Loading from '../components/common/Loading';

function Builder() {
  const { projectId } = useParams();
  const navigate = useNavigate();
  const { user, project: contextProject, setProject } = useContext(AuthContext);

  const [project, setLocalProject] = useState(null);
  const [pages, setPages] = useState([]);
  const [currentPage, setCurrentPage] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [publishing, setPublishing] = useState(false);
  const [activeTab, setActiveTab] = useState('blocks');
  const [toasts, setToasts] = useState([]);
  const [editor, setEditor] = useState(null);

  const showToast = useCallback((message, type = 'info') => {
    const id = Date.now();
    setToasts(prev => [...prev, { id, message, type }]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 3000);
  }, []);

  useEffect(() => {
    const loadProject = async () => {
      try {
        setLoading(true);
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

  const handleSave = useCallback(async () => {
    if (!editor || !currentPage || !project) return;

    try {
      setSaving(true);

      const grapesjs_data = {
        components: editor.getComponents().map(c => c.toJSON()),
        styles: editor.getStyle().map(s => s.toJSON()),
        assets: editor.AssetManager.getAll().map(a => a.toJSON())
      };

      await pageApi.update(project.id, currentPage.id, { grapesjs_data });

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

  const handlePublish = useCallback(async () => {
    if (!project) return;

    try {
      setPublishing(true);
      await handleSave();

      const response = await projectApi.publish(project.id);

      if (response.data.success) {
        showToast('Site publié avec succès !', 'success');
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

  const handlePageChange = useCallback(async (page) => {
    if (page.id === currentPage?.id) return;

    try {
      if (editor && currentPage) {
        await handleSave();
      }

      const response = await pageApi.get(project.id, page.id);
      
      if (response.data.success) {
        const pageData = response.data.data.page;
        setCurrentPage(pageData);

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

  const handleAddPage = useCallback(async (name) => {
    if (!project) return;

    try {
      const response = await pageApi.create(project.id, { name });
      
      if (response.data.success) {
        const newPage = response.data.data.page;
        setPages(prev => [...prev, newPage]);
        showToast('Page créée avec succès', 'success');
        handlePageChange(newPage);
      }
    } catch (error) {
      console.error('Erreur création page:', error);
      showToast(`Erreur: ${error.response?.data?.message || 'Création échouée'}`, 'error');
    }
  }, [project, handlePageChange, showToast]);

  const handleDeletePage = useCallback(async (pageId) => {
    if (!project || pages.length <= 1) {
      showToast('Impossible de supprimer la dernière page', 'warning');
      return;
    }

    try {
      await pageApi.delete(project.id, pageId);
      const updatedPages = pages.filter(p => p.id !== pageId);
      setPages(updatedPages);
      
      if (currentPage?.id === pageId) {
        handlePageChange(updatedPages[0]);
      }
      
      showToast('Page supprimée', 'success');
    } catch (error) {
      console.error('Erreur suppression:', error);
      showToast('Erreur lors de la suppression', 'error');
    }
  }, [project, pages, currentPage, handlePageChange, showToast]);

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

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-editor-bg">
        <Loading message="Chargement du builder..." />
      </div>
    );
  }

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
        {/* Sidebar gauche */}
        <div className="builder-sidebar">
          <div className="sidebar-tabs">
            <button
              className={`sidebar-tab ${activeTab === 'blocks' ? 'active' : ''}`}
              onClick={() => setActiveTab('blocks')}
            >
              🧱 Blocs
            </button>
            <button
              className={`sidebar-tab ${activeTab === 'pages' ? 'active' : ''}`}
              onClick={() => setActiveTab('pages')}
            >
              📄 Pages
            </button>
            <button
              className={`sidebar-tab ${activeTab === 'layers' ? 'active' : ''}`}
              onClick={() => setActiveTab('layers')}
            >
              📚 Couches
            </button>
          </div>

          <div className="sidebar-content">
            {/* 
              IMPORTANT: On garde tous les containers montés mais on les masque visuellement
              Cela évite le bug où GrapesJS ne peut pas render dans un container display:none
            */}
            <div 
              id="blocks-container" 
              className="sidebar-panel"
              style={{ 
                display: activeTab === 'blocks' ? 'block' : 'none'
              }}
            />
            
            <div 
              id="layers-container" 
              className="sidebar-panel"
              style={{ 
                display: activeTab === 'layers' ? 'block' : 'none'
              }}
            />

            <div
              className="sidebar-panel"
              style={{ 
                display: activeTab === 'pages' ? 'block' : 'none'
              }}
            >
              <PageManager
                pages={pages}
                currentPage={currentPage}
                onPageSelect={handlePageChange}
                onAddPage={handleAddPage}
                onDeletePage={handleDeletePage}
                onDuplicatePage={handleDuplicatePage}
              />
            </div>
          </div>
        </div>

        {/* Zone d'édition */}
        <div className="editor-canvas">
          <GrapesEditor
            currentPage={currentPage}
            projectSettings={project.settings}
            onEditorReady={setEditor}
          />
        </div>

        {/* Panneau droit - Styles */}
        <div className="builder-panel-right">
          <div className="panel-section">
            <h3 className="panel-title">🎨 Styles</h3>
            <div id="styles-container"></div>
          </div>
          <div className="panel-section">
            <h3 className="panel-title">⚙️ Propriétés</h3>
            <div id="traits-container"></div>
          </div>
          <div id="selectors-container" style={{ display: 'none' }}></div>
        </div>
      </div>

      {/* Notifications */}
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
