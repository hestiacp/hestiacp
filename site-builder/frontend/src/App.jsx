/**
 * ===========================================
 * Composant principal de l'application
 * ===========================================
 * 
 * Gère le routage et l'état d'authentification global.
 */

import { Routes, Route, Navigate } from 'react-router-dom';
import { useState, useEffect, createContext } from 'react';

// Pages
import Builder from './pages/Builder';
import AuthCallback from './pages/AuthCallback';
import ErrorPage from './pages/ErrorPage';
import Loading from './components/common/Loading';

// Services
import { api, setAuthToken, getStoredToken } from './services/api';

// Contexte d'authentification global
export const AuthContext = createContext(null);

function App() {
  const [user, setUser] = useState(null);
  const [project, setProject] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  /**
   * Vérifie l'authentification au chargement
   */
  useEffect(() => {
    const checkAuth = async () => {
      const token = getStoredToken();
      
      if (!token) {
        setLoading(false);
        return;
      }

      try {
        setAuthToken(token);
        const response = await api.get('/auth/me');
        
        if (response.data.success) {
          setUser(response.data.data.user);
          
          // Charger le premier projet si disponible
          if (response.data.data.projects?.length > 0) {
            setProject(response.data.data.projects[0]);
          }
        }
      } catch (err) {
        console.error('Erreur d\'authentification:', err);
        // Token invalide, le supprimer
        localStorage.removeItem('sitebuilder_token');
        setAuthToken(null);
      }

      setLoading(false);
    };

    checkAuth();
  }, []);

  /**
   * Fonction de connexion
   */
  const login = (token, userData, projectData) => {
    localStorage.setItem('sitebuilder_token', token);
    setAuthToken(token);
    setUser(userData);
    setProject(projectData);
  };

  /**
   * Fonction de déconnexion
   */
  const logout = () => {
    localStorage.removeItem('sitebuilder_token');
    setAuthToken(null);
    setUser(null);
    setProject(null);
  };

  // Affichage du chargement initial
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <Loading message="Chargement du Site Builder..." />
      </div>
    );
  }

  return (
    <AuthContext.Provider value={{ user, setUser, project, setProject, login, logout }}>
      <Routes>
        {/* Callback d'authentification SSO */}
        <Route path="/auth/callback" element={<AuthCallback />} />
        
        {/* Page d'erreur */}
        <Route path="/error" element={<ErrorPage />} />
        
        {/* Builder principal */}
        <Route 
          path="/builder/:projectId?" 
          element={
            user ? (
              <Builder />
            ) : (
              <Navigate to="/error?message=Non authentifié" replace />
            )
          } 
        />
        
        {/* Redirection par défaut */}
        <Route 
          path="/" 
          element={
            user && project ? (
              <Navigate to={`/builder/${project.id}`} replace />
            ) : (
              <div className="min-h-screen flex items-center justify-center bg-gray-100">
                <div className="text-center">
                  <h1 className="text-2xl font-bold text-gray-800 mb-4">
                    HestiaCP Site Builder
                  </h1>
                  <p className="text-gray-600 mb-4">
                    Veuillez vous connecter via HestiaCP pour accéder au builder.
                  </p>
                  <p className="text-sm text-gray-500">
                    Connectez-vous à votre panneau HestiaCP et cliquez sur "Site Builder"
                    dans la gestion de votre domaine.
                  </p>
                </div>
              </div>
            )
          } 
        />
        
        {/* Route 404 */}
        <Route 
          path="*" 
          element={<Navigate to="/error?message=Page non trouvée" replace />} 
        />
      </Routes>
    </AuthContext.Provider>
  );
}

export default App;
