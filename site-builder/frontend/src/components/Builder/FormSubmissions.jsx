/**
 * ===========================================
 * Visualiseur des soumissions de formulaires
 * ===========================================
 */

import { useState, useEffect } from 'react';
import { Mail, Trash2, Eye, Check, Archive, X, RefreshCw, Filter } from 'lucide-react';
import { formApi } from '../../services/api';

function FormSubmissions({ projectId, onClose }) {
  const [submissions, setSubmissions] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [selectedSubmission, setSelectedSubmission] = useState(null);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    loadSubmissions();
  }, [projectId, filter]);

  const loadSubmissions = async () => {
    setLoading(true);
    try {
      const params = filter !== 'all' ? { status: filter } : {};
      const response = await formApi.listSubmissions(projectId, params);
      if (response.data.success) {
        setSubmissions(response.data.data.submissions);
        setTotal(response.data.data.total);
      }
    } catch (error) {
      console.error('Erreur chargement soumissions:', error);
    } finally {
      setLoading(false);
    }
  };

  const updateStatus = async (submissionId, status) => {
    try {
      await formApi.updateSubmission(projectId, submissionId, { status });
      setSubmissions(prev => 
        prev.map(s => s.id === submissionId ? { ...s, status } : s)
      );
    } catch (error) {
      console.error('Erreur mise à jour:', error);
    }
  };

  const deleteSubmission = async (submissionId) => {
    if (!confirm('Supprimer cette soumission ?')) return;
    
    try {
      await formApi.deleteSubmission(projectId, submissionId);
      setSubmissions(prev => prev.filter(s => s.id !== submissionId));
      setTotal(prev => prev - 1);
      if (selectedSubmission?.id === submissionId) {
        setSelectedSubmission(null);
      }
    } catch (error) {
      console.error('Erreur suppression:', error);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const statusColors = {
    new: '#3b82f6',
    read: '#6b7280',
    replied: '#10b981',
    archived: '#9ca3af',
    spam: '#ef4444'
  };

  const statusLabels = {
    new: 'Nouveau',
    read: 'Lu',
    replied: 'Répondu',
    archived: 'Archivé',
    spam: 'Spam'
  };

  return (
    <div className="submissions-panel">
      <div className="panel-header">
        <h3><Mail size={18} /> Messages ({total})</h3>
        <button onClick={onClose} className="close-btn">
          <X size={18} />
        </button>
      </div>

      {/* Filtres */}
      <div className="filters">
        <button onClick={loadSubmissions} className="refresh-btn">
          <RefreshCw size={16} />
        </button>
        <select value={filter} onChange={(e) => setFilter(e.target.value)}>
          <option value="all">Tous</option>
          <option value="new">Nouveaux</option>
          <option value="read">Lus</option>
          <option value="replied">Répondus</option>
          <option value="archived">Archivés</option>
        </select>
      </div>

      <div className="panel-content">
        {loading ? (
          <div className="loading">Chargement...</div>
        ) : submissions.length === 0 ? (
          <div className="empty">
            <Mail size={48} />
            <p>Aucun message pour le moment</p>
          </div>
        ) : (
          <div className="submissions-layout">
            {/* Liste */}
            <div className="submissions-list">
              {submissions.map(submission => (
                <div 
                  key={submission.id}
                  className={`submission-item ${selectedSubmission?.id === submission.id ? 'selected' : ''} ${submission.status === 'new' ? 'unread' : ''}`}
                  onClick={() => {
                    setSelectedSubmission(submission);
                    if (submission.status === 'new') {
                      updateStatus(submission.id, 'read');
                    }
                  }}
                >
                  <div className="submission-header">
                    <span 
                      className="status-dot" 
                      style={{ background: statusColors[submission.status] }}
                    />
                    <span className="submission-email">
                      {submission.data.email || submission.data.Email || 'Sans email'}
                    </span>
                  </div>
                  <div className="submission-preview">
                    {submission.data.message || submission.data.Message || Object.values(submission.data).slice(0, 2).join(' - ')}
                  </div>
                  <div className="submission-date">
                    {formatDate(submission.created_at)}
                  </div>
                </div>
              ))}
            </div>

            {/* Détail */}
            {selectedSubmission && (
              <div className="submission-detail">
                <div className="detail-header">
                  <span 
                    className="status-badge"
                    style={{ background: statusColors[selectedSubmission.status] }}
                  >
                    {statusLabels[selectedSubmission.status]}
                  </span>
                  <div className="detail-actions">
                    <button 
                      onClick={() => updateStatus(selectedSubmission.id, 'replied')}
                      title="Marquer comme répondu"
                    >
                      <Check size={16} />
                    </button>
                    <button 
                      onClick={() => updateStatus(selectedSubmission.id, 'archived')}
                      title="Archiver"
                    >
                      <Archive size={16} />
                    </button>
                    <button 
                      onClick={() => deleteSubmission(selectedSubmission.id)}
                      className="delete-btn"
                      title="Supprimer"
                    >
                      <Trash2 size={16} />
                    </button>
                  </div>
                </div>

                <div className="detail-content">
                  <div className="detail-date">
                    Reçu le {formatDate(selectedSubmission.created_at)}
                  </div>

                  <div className="detail-fields">
                    {Object.entries(selectedSubmission.data).map(([key, value]) => (
                      <div key={key} className="field">
                        <label>{key}</label>
                        <div className="value">{value}</div>
                      </div>
                    ))}
                  </div>

                  {selectedSubmission.visitor_info && (
                    <div className="visitor-info">
                      <h4>Informations visiteur</h4>
                      <p>IP: {selectedSubmission.visitor_info.ip || 'N/A'}</p>
                      <p>Page: {selectedSubmission.visitor_info.referer || 'N/A'}</p>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        )}
      </div>

      <style>{`
        .submissions-panel {
          position: fixed;
          right: 0;
          top: 56px;
          bottom: 0;
          width: 700px;
          background: #16213e;
          border-left: 1px solid #0f3460;
          display: flex;
          flex-direction: column;
          z-index: 100;
          animation: slideIn 0.2s ease;
        }
        
        @keyframes slideIn {
          from { transform: translateX(100%); }
          to { transform: translateX(0); }
        }
        
        .panel-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .panel-header h3 {
          display: flex;
          align-items: center;
          gap: 8px;
          margin: 0;
          color: #fff;
          font-size: 16px;
        }
        
        .close-btn {
          background: none;
          border: none;
          color: #9ca3af;
          cursor: pointer;
        }
        
        .filters {
          display: flex;
          gap: 8px;
          padding: 12px 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .refresh-btn {
          padding: 8px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #9ca3af;
          cursor: pointer;
        }
        
        .filters select {
          flex: 1;
          padding: 8px 12px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #e4e4e7;
          font-size: 14px;
        }
        
        .panel-content {
          flex: 1;
          overflow: hidden;
        }
        
        .loading, .empty {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          height: 100%;
          color: #6b7280;
        }
        
        .empty p {
          margin-top: 16px;
        }
        
        .submissions-layout {
          display: flex;
          height: 100%;
        }
        
        .submissions-list {
          width: 280px;
          border-right: 1px solid #0f3460;
          overflow-y: auto;
        }
        
        .submission-item {
          padding: 16px;
          border-bottom: 1px solid #0f3460;
          cursor: pointer;
          transition: background 0.2s;
        }
        
        .submission-item:hover {
          background: rgba(255,255,255,0.03);
        }
        
        .submission-item.selected {
          background: rgba(59,130,246,0.1);
        }
        
        .submission-item.unread {
          border-left: 3px solid #3b82f6;
        }
        
        .submission-header {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 6px;
        }
        
        .status-dot {
          width: 8px;
          height: 8px;
          border-radius: 50%;
        }
        
        .submission-email {
          color: #fff;
          font-size: 14px;
          font-weight: 500;
        }
        
        .submission-preview {
          color: #9ca3af;
          font-size: 13px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          margin-bottom: 6px;
        }
        
        .submission-date {
          color: #6b7280;
          font-size: 12px;
        }
        
        .submission-detail {
          flex: 1;
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        
        .detail-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #0f3460;
        }
        
        .status-badge {
          padding: 4px 12px;
          border-radius: 12px;
          color: white;
          font-size: 12px;
          font-weight: 500;
        }
        
        .detail-actions {
          display: flex;
          gap: 8px;
        }
        
        .detail-actions button {
          padding: 8px;
          background: rgba(255,255,255,0.05);
          border: 1px solid #0f3460;
          border-radius: 6px;
          color: #9ca3af;
          cursor: pointer;
        }
        
        .detail-actions button:hover {
          background: rgba(255,255,255,0.1);
          color: #fff;
        }
        
        .delete-btn:hover {
          background: rgba(239, 68, 68, 0.2) !important;
          color: #ef4444 !important;
        }
        
        .detail-content {
          flex: 1;
          padding: 16px;
          overflow-y: auto;
        }
        
        .detail-date {
          color: #6b7280;
          font-size: 13px;
          margin-bottom: 20px;
        }
        
        .detail-fields {
          display: flex;
          flex-direction: column;
          gap: 16px;
        }
        
        .field label {
          display: block;
          color: #9ca3af;
          font-size: 12px;
          text-transform: uppercase;
          letter-spacing: 1px;
          margin-bottom: 6px;
        }
        
        .field .value {
          color: #fff;
          font-size: 15px;
          line-height: 1.6;
          padding: 12px;
          background: rgba(255,255,255,0.03);
          border-radius: 8px;
          white-space: pre-wrap;
        }
        
        .visitor-info {
          margin-top: 24px;
          padding-top: 24px;
          border-top: 1px solid #0f3460;
        }
        
        .visitor-info h4 {
          color: #9ca3af;
          font-size: 12px;
          text-transform: uppercase;
          letter-spacing: 1px;
          margin: 0 0 12px 0;
        }
        
        .visitor-info p {
          color: #6b7280;
          font-size: 13px;
          margin: 4px 0;
        }
      `}</style>
    </div>
  );
}

export default FormSubmissions;
