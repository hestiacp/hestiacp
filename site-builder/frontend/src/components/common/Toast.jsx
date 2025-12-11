/**
 * ===========================================
 * Composant Toast (notification)
 * ===========================================
 * 
 * Affiche une notification temporaire.
 */

import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-react';

function Toast({ message, type = 'info', onClose }) {
  const icons = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info
  };

  const Icon = icons[type] || icons.info;

  return (
    <div className={`toast toast-${type}`}>
      <Icon size={18} />
      <span className="flex-1">{message}</span>
      {onClose && (
        <button onClick={onClose} className="ml-2 hover:opacity-80">
          <X size={16} />
        </button>
      )}
    </div>
  );
}

export default Toast;
