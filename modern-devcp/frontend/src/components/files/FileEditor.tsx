import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { X, Save, Download, Undo, Redo, Search, Replace } from 'lucide-react'
import { FileItem } from '../../types/files'
import { formatFileSize, isEditableFile } from '../../utils/fileUtils'

interface FileEditorProps {
  file: FileItem | null
  isOpen: boolean
  onClose: () => void
  onSave?: (file: FileItem, content: string) => void
}

const FileEditor: React.FC<FileEditorProps> = ({
  file,
  isOpen,
  onClose,
  onSave
}) => {
  const [content, setContent] = useState('')
  const [originalContent, setOriginalContent] = useState('')
  const [hasChanges, setHasChanges] = useState(false)
  const [loading, setLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [showFindReplace, setShowFindReplace] = useState(false)
  const [findText, setFindText] = useState('')
  const [replaceText, setReplaceText] = useState('')

  useEffect(() => {
    if (file && isOpen && isEditableFile(file)) {
      loadFileContent()
    }
  }, [file, isOpen])

  useEffect(() => {
    setHasChanges(content !== originalContent)
  }, [content, originalContent])

  const loadFileContent = async () => {
    if (!file) return
    
    setLoading(true)
    try {
      // Simulate loading file content
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const mockContent = getMockContent(file)
      setContent(mockContent)
      setOriginalContent(mockContent)
    } catch (error) {
      console.error('Error loading file content:', error)
    } finally {
      setLoading(false)
    }
  }

  const getMockContent = (file: FileItem): string => {
    const ext = file.extension?.toLowerCase()
    
    switch (ext) {
      case 'json':
        return JSON.stringify({
          name: "DevCP Configuration",
          version: "2.0.0",
          description: "Modern hosting control panel",
          features: ["React", "TypeScript", "Tailwind CSS"],
          settings: {
            theme: "auto",
            language: "fr",
            debug: false
          },
          database: {
            host: "localhost",
            port: 5432,
            name: "devcp"
          }
        }, null, 2)
      
      case 'html':
        return `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevCP - Panneau de Contrôle</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Bienvenue sur DevCP</h1>
            <p>Votre panneau de contrôle d'hébergement moderne.</p>
        </header>
        
        <main>
            <section>
                <h2>Fonctionnalités</h2>
                <ul>
                    <li>Gestion des sites web</li>
                    <li>Bases de données</li>
                    <li>Comptes email</li>
                    <li>Gestionnaire de fichiers</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>`
      
      case 'css':
        return `/* DevCP - Modern Hosting Control Panel Styles */

:root {
  --primary-color: #667eea;
  --secondary-color: #764ba2;
  --background-color: #f8fafc;
  --text-color: #1a202c;
  --border-color: #e2e8f0;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background-color: var(--background-color);
  color: var(--text-color);
  line-height: 1.6;
}

.devcp-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.devcp-header {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
  color: white;
  padding: 2rem;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
}

.devcp-card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid var(--border-color);
}

.devcp-button {
  background: var(--primary-color);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.devcp-button:hover {
  background: var(--secondary-color);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .devcp-container {
    padding: 1rem;
  }
  
  .devcp-header {
    padding: 1.5rem;
  }
}`
      
      case 'js':
      case 'ts':
        return `// DevCP - Modern Hosting Control Panel
import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'

interface DevCPProps {
  title?: string
  theme?: 'light' | 'dark'
}

const DevCPComponent: React.FC<DevCPProps> = ({ 
  title = 'DevCP Dashboard',
  theme = 'light'
}) => {
  const [isLoading, setIsLoading] = useState(false)
  const [data, setData] = useState<any[]>([])
  const [error, setError] = useState<string | null>(null)
  
  useEffect(() => {
    loadData()
  }, [])
  
  const loadData = async () => {
    setIsLoading(true)
    setError(null)
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      const mockData = [
        { id: 1, name: 'Site Web 1', status: 'active' },
        { id: 2, name: 'Site Web 2', status: 'inactive' },
        { id: 3, name: 'Site Web 3', status: 'active' }
      ]
      
      setData(mockData)
    } catch (err) {
      setError('Erreur lors du chargement des données')
      console.error('Error loading data:', err)
    } finally {
      setIsLoading(false)
    }
  }
  
  const handleAction = async (id: number) => {
    try {
      console.log(\`Action performed on item \${id}\`)
      // Perform action
      await loadData() // Refresh data
    } catch (error) {
      console.error('Error performing action:', error)
    }
  }
  
  if (error) {
    return (
      <div className="error-container">
        <p>Erreur: {error}</p>
        <button onClick={loadData}>Réessayer</button>
      </div>
    )
  }
  
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className={\`devcp-container theme-\${theme}\`}
    >
      <header className="devcp-header">
        <h1>{title}</h1>
        <p>Panneau de contrôle d'hébergement moderne</p>
      </header>
      
      <main className="devcp-main">
        {isLoading ? (
          <div className="loading-spinner">
            <p>Chargement...</p>
          </div>
        ) : (
          <div className="data-grid">
            <AnimatePresence>
              {data.map((item, index) => (
                <motion.div
                  key={item.id}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 20 }}
                  transition={{ delay: index * 0.1 }}
                  className="data-item"
                >
                  <h3>{item.name}</h3>
                  <p>Status: {item.status}</p>
                  <button 
                    onClick={() => handleAction(item.id)}
                    className="action-button"
                  >
                    Action
                  </button>
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </main>
    </motion.div>
  )
}

export default DevCPComponent`
      
      default:
        return `# ${file.name}

Ce fichier peut être édité dans l'éditeur DevCP.

## Informations
- Nom: ${file.name}
- Taille: ${formatFileSize(file.size)}
- Type: ${file.extension?.toUpperCase() || 'Fichier texte'}

## Contenu

Vous pouvez modifier ce contenu et sauvegarder vos changements.

Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.

Ut enim ad minim veniam, quis nostrud exercitation ullamco 
laboris nisi ut aliquip ex ea commodo consequat.

Duis aute irure dolor in reprehenderit in voluptate velit 
esse cillum dolore eu fugiat nulla pariatur.

Excepteur sint occaecat cupidatat non proident, sunt in 
culpa qui officia deserunt mollit anim id est laborum.`
    }
  }

  const handleSave = async () => {
    if (!file || !hasChanges) return
    
    setSaving(true)
    try {
      await new Promise(resolve => setTimeout(resolve, 1000))
      onSave?.(file, content)
      setOriginalContent(content)
      setHasChanges(false)
    } catch (error) {
      console.error('Error saving file:', error)
    } finally {
      setSaving(false)
    }
  }

  const handleUndo = () => {
    setContent(originalContent)
  }

  const handleFind = () => {
    if (!findText) return
    
    const textarea = document.getElementById('file-editor-textarea') as HTMLTextAreaElement
    if (textarea) {
      const index = content.toLowerCase().indexOf(findText.toLowerCase())
      if (index !== -1) {
        textarea.focus()
        textarea.setSelectionRange(index, index + findText.length)
      }
    }
  }

  const handleReplace = () => {
    if (!findText) return
    
    const newContent = content.replace(new RegExp(findText, 'gi'), replaceText)
    setContent(newContent)
  }

  const handleReplaceAll = () => {
    if (!findText) return
    
    const newContent = content.replace(new RegExp(findText, 'gi'), replaceText)
    setContent(newContent)
  }

  if (!file || !isEditableFile(file)) return null

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 z-50 flex flex-col bg-white dark:bg-gray-900"
        >
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div className="flex items-center space-x-3">
              <span className="text-xl">📝</span>
              <div>
                <h3 className="font-medium text-gray-900 dark:text-white">{file.name}</h3>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {formatFileSize(file.size)} • {hasChanges ? 'Modifié' : 'Sauvegardé'}
                </p>
              </div>
            </div>
            
            <div className="flex items-center space-x-2">
              <button
                onClick={() => setShowFindReplace(!showFindReplace)}
                className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                title="Rechercher et remplacer"
              >
                <Search className="w-4 h-4" />
              </button>
              
              <button
                onClick={handleUndo}
                disabled={!hasChanges}
                className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                title="Annuler les modifications"
              >
                <Undo className="w-4 h-4" />
              </button>
              
              <button
                onClick={handleSave}
                disabled={!hasChanges || saving}
                className="flex items-center space-x-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Save className="w-4 h-4" />
                <span>{saving ? 'Sauvegarde...' : 'Sauvegarder'}</span>
              </button>
              
              <button
                onClick={onClose}
                className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                title="Fermer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>
          </div>

          {/* Find/Replace Bar */}
          <AnimatePresence>
            {showFindReplace && (
              <motion.div
                initial={{ height: 0, opacity: 0 }}
                animate={{ height: 'auto', opacity: 1 }}
                exit={{ height: 0, opacity: 0 }}
                className="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-4"
              >
                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    <input
                      type="text"
                      placeholder="Rechercher..."
                      value={findText}
                      onChange={(e) => setFindText(e.target.value)}
                      className="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                    <button
                      onClick={handleFind}
                      className="px-3 py-1 bg-primary-600 text-white rounded text-sm hover:bg-primary-700"
                    >
                      Rechercher
                    </button>
                  </div>
                  
                  <div className="flex items-center space-x-2">
                    <input
                      type="text"
                      placeholder="Remplacer par..."
                      value={replaceText}
                      onChange={(e) => setReplaceText(e.target.value)}
                      className="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm"
                    />
                    <button
                      onClick={handleReplace}
                      className="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
                    >
                      Remplacer
                    </button>
                    <button
                      onClick={handleReplaceAll}
                      className="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700"
                    >
                      Tout remplacer
                    </button>
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>

          {/* Editor */}
          <div className="flex-1 overflow-hidden">
            {loading ? (
              <div className="flex items-center justify-center h-full">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>
            ) : (
              <textarea
                id="file-editor-textarea"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                className="w-full h-full p-4 font-mono text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 border-none outline-none resize-none"
                placeholder="Commencez à taper..."
                spellCheck={false}
              />
            )}
          </div>

          {/* Status Bar */}
          <div className="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
            <div className="flex items-center space-x-4">
              <span>Lignes: {content.split('\n').length}</span>
              <span>Caractères: {content.length}</span>
              <span>Type: {file.extension?.toUpperCase() || 'TXT'}</span>
            </div>
            
            <div className="flex items-center space-x-2">
              {hasChanges && (
                <span className="text-orange-500">Non sauvegardé</span>
              )}
              <span>UTF-8</span>
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}

export default FileEditor