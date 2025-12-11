/**
 * ===========================================
 * Page de callback d'authentification SSO
 * ===========================================
 * 
 * Reçoit le token et l'ID du projet depuis l'URL
 * après redirection du backend SSO.
 */

import { useEffect, useContext, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { AuthContext } from '../App';
import { setAuthToken, authApi } from '../services/api';
import Loading from '../components/common/Loading';

function AuthCallback() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const { login } = useContext(AuthContext);
  const [error, setError] = useState(null);

  useEffect(() => {
    const handleAuth = async () => {
      // Récupérer les paramètres de l'URL
      const token = searchParams.get('token');
      const projectId = searchParams.get('projectId');

      if (!token) {
        setError('Token d\'authentification manquant');
        return;
      }

      try {
        // Configurer le token
        localStorage.setItem('sitebuilder_token', token);
        setAuthToken(token);

        // Récupérer les informations de l'utilisateur
        const response = await authApi.getCurrentUser();

        if (response.data.success) {
          const { user, projects } = response.data.data;
          
          // Trouver le projet spécifié ou prendre le premier
          let project = null;
          if (projectId) {
            project = projects.find(p => p.id === projectId);
          }
          if (!project && projects.length > 0) {
            project = projects[0];
          }

          // Connecter l'utilisateur
          login(token, user, project);

          // Rediriger vers le builder
          navigate(`/builder/${project?.id || ''}`, { replace: true });
        } else {
          setError('Erreur lors de la récupération des données utilisateur');
        }
      } catch (err) {
        console.error('Erreur d\'authentification:', err);
        setError(err.response?.data?.message || 'Erreur d\'authentification');
        
        // Nettoyer le token invalide
        localStorage.removeItem('sitebuilder_token');
        setAuthToken(null);
      }
    };

    handleAuth();
  }, [searchParams, login, navigate]);

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-100">
        <div className="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
          <div className="text-red-500 text-5xl mb-4">⚠️</div>
          <h1 className="text-xl font-bold text-gray-800 mb-2">
            Erreur d'authentification
          </h1>
          <p className="text-gray-600 mb-4">{error}</p>
          <a
            href="/"
            className="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Retour à l'accueil
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <Loading message="Authentification en cours..." />
    </div>
  );
}

export default AuthCallback;
