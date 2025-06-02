import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { X, Download, Edit, Maximize2, Minimize2 } from 'lucide-react'
import { FileItem } from '../../types/files'
import { formatFileSize, formatDate, isImageFile, isTextFile } from '../../utils/fileUtils'

interface FilePreviewProps {
  file: FileItem | null
  isOpen: boolean
  onClose: () => void
  onEdit?: (file: FileItem) => void
  onDownload?: (file: FileItem) => void
}

const FilePreview: React.FC<FilePreviewProps> = ({
  file,
  isOpen,
  onClose,
  onEdit,
  onDownload
}) => {
  const [isFullscreen, setIsFullscreen] = useState(false)
  const [fileContent, setFileContent] = useState<string>('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (file && isTextFile(file) && isOpen) {
      loadFileContent()
    }
  }, [file, isOpen])

  const loadFileContent = async () => {
    if (!file) return
    
    setLoading(true)
    try {
      // Simulate loading file content
      await new Promise(resolve => setTimeout(resolve, 500))
      
      // Mock content based on file type
      const mockContent = getMockContent(file)
      setFileContent(mockContent)
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
            language: "fr"
          }
        }, null, 2)
      
      case 'html':
        return `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevCP - Panneau de Contrôle</title>
</head>
<body>
    <h1>Bienvenue sur DevCP</h1>
    <p>Votre panneau de contrôle d'hébergement moderne.</p>
</body>
</html>`
      
      case 'css':
        return `.devcp-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.devcp-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 2rem;
  border-radius: 0.5rem;
}

.devcp-card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
}`
      
      case 'js':
      case 'ts':
        return `// DevCP - Modern Hosting Control Panel
import React from 'react'
import { motion } from 'framer-motion'

const DevCPComponent = () => {
  const [isLoading, setIsLoading] = useState(false)
  
  const handleAction = async () => {
    setIsLoading(true)
    try {
      // Perform action
      console.log('Action performed successfully')
    } catch (error) {
      console.error('Error:', error)
    } finally {
      setIsLoading(false)
    }
  }
  
  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      className="devcp-container"
    >
      <h1>DevCP Dashboard</h1>
      <button onClick={handleAction} disabled={isLoading}>
        {isLoading ? 'Loading...' : 'Execute'}
      </button>
    </motion.div>
  )
}

export default DevCPComponent`
      
      default:
        return `# ${file.name}

Ce fichier contient du contenu textuel.

## Informations
- Nom: ${file.name}
- Taille: ${formatFileSize(file.size)}
- Modifié: ${formatDate(file.lastModified)}
- Type: ${file.extension?.toUpperCase() || 'Fichier'}

## Contenu
Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.

Ut enim ad minim veniam, quis nostrud exercitation ullamco 
laboris nisi ut aliquip ex ea commodo consequat.`
    }
  }

  const renderPreviewContent = () => {
    if (!file) return null

    if (isImageFile(file)) {
      return (
        <div className="flex items-center justify-center h-full bg-gray-50 dark:bg-gray-900">
          <div className="text-center">
            <div className="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
              <span className="text-white text-4xl">🖼️</span>
            </div>
            <p className="text-gray-500 dark:text-gray-400">Aperçu d'image</p>
            <p className="text-sm text-gray-400 dark:text-gray-500">{file.name}</p>
          </div>
        </div>
      )
    }

    if (isTextFile(file)) {
      return (
        <div className="h-full flex flex-col">
          <div className="flex-1 overflow-auto">
            {loading ? (
              <div className="flex items-center justify-center h-full">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
              </div>
            ) : (
              <pre className="p-4 text-sm font-mono text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                {fileContent}
              </pre>
            )}
          </div>
        </div>
      )
    }

    // Default preview for other file types
    return (
      <div className="flex items-center justify-center h-full">
        <div className="text-center">
          <div className="w-24 h-24 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
            <span className="text-4xl">📄</span>
          </div>
          <p className="text-gray-500 dark:text-gray-400">Aperçu non disponible</p>
          <p className="text-sm text-gray-400 dark:text-gray-500">
            Type de fichier: {file.extension?.toUpperCase() || 'Inconnu'}
          </p>
        </div>
      </div>
    )
  }

  if (!file) return null

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className={`fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 ${
            isFullscreen ? 'p-0' : ''
          }`}
          onClick={onClose}
        >
          <motion.div
            initial={{ scale: 0.9, opacity: 0 }}
            animate={{ scale: 1, opacity: 1 }}
            exit={{ scale: 0.9, opacity: 0 }}
            className={`bg-white dark:bg-gray-800 rounded-lg shadow-xl flex flex-col ${
              isFullscreen 
                ? 'w-full h-full rounded-none' 
                : 'w-full max-w-4xl h-[80vh]'
            }`}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
              <div className="flex items-center space-x-3">
                <span className="text-2xl">{isImageFile(file) ? '🖼️' : '📄'}</span>
                <div>
                  <h3 className="font-medium text-gray-900 dark:text-white">{file.name}</h3>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {formatFileSize(file.size)} • {formatDate(file.lastModified)}
                  </p>
                </div>
              </div>
              
              <div className="flex items-center space-x-2">
                {isTextFile(file) && (
                  <button
                    onClick={() => onEdit?.(file)}
                    className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="Éditer"
                  >
                    <Edit className="w-4 h-4" />
                  </button>
                )}
                
                <button
                  onClick={() => onDownload?.(file)}
                  className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                  title="Télécharger"
                >
                  <Download className="w-4 h-4" />
                </button>
                
                <button
                  onClick={() => setIsFullscreen(!isFullscreen)}
                  className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                  title={isFullscreen ? "Réduire" : "Plein écran"}
                >
                  {isFullscreen ? <Minimize2 className="w-4 h-4" /> : <Maximize2 className="w-4 h-4" />}
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

            {/* Content */}
            <div className="flex-1 overflow-hidden bg-white dark:bg-gray-800">
              {renderPreviewContent()}
            </div>
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}

export default FilePreview