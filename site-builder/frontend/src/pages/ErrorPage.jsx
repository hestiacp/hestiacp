/**
 * ===========================================
 * Page d'erreur générique
 * ===========================================
 * 
 * Affiche un message d'erreur personnalisable.
 */

import { useSearchParams, useNavigate } from 'react-router-dom';
import { AlertTriangle, ArrowLeft, Home } from 'lucide-react';

function ErrorPage() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  
  const message = searchParams.get('message') || 'Une erreur est survenue';
  const code = searchParams.get('code') || '500';

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100">
      <div className="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        {/* Icône d'erreur */}
        <div className="flex justify-center mb-4">
          <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
            <AlertTriangle className="w-8 h-8 text-red-500" />
          </div>
        </div>

        {/* Code d'erreur */}
        <div className="text-6xl font-bold text-gray-200 mb-2">
          {code}
        </div>

        {/* Message */}
        <h1 className="text-xl font-bold text-gray-800 mb-2">
          Oops !
        </h1>
        <p className="text-gray-600 mb-6">
          {message}
        </p>

        {/* Actions */}
        <div className="flex flex-col sm:flex-row gap-3 justify-center">
          <button
            onClick={() => navigate(-1)}
            className="inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
            Retour
          </button>
          
          <a
            href="/"
            className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
          >
            <Home className="w-4 h-4" />
            Accueil
          </a>
        </div>

        {/* Info de contact */}
        <p className="text-sm text-gray-400 mt-6">
          Si le problème persiste, contactez votre administrateur.
        </p>
      </div>
    </div>
  );
}

export default ErrorPage;
