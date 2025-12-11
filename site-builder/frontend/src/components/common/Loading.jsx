/**
 * ===========================================
 * Composant de chargement
 * ===========================================
 * 
 * Affiche un spinner de chargement avec un message optionnel.
 */

import { Loader2 } from 'lucide-react';

function Loading({ message = 'Chargement...' }) {
  return (
    <div className="flex flex-col items-center justify-center gap-4">
      <Loader2 className="w-10 h-10 text-primary-500 animate-spin" />
      <p className="text-gray-600 text-sm">{message}</p>
    </div>
  );
}

export default Loading;
