import React, { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { useFilesStore } from '../../stores/filesStore'
import { FileItem } from '../../types/files'
import { generateUniqueFileName } from '../../utils/fileUtils'
import FileToolbar from '../../components/files/FileToolbar'
import FileList from '../../components/files/FileList'
import FileGrid from '../../components/files/FileGrid'
import FileUploadZone from '../../components/files/FileUploadZone'
import FilePreview from '../../components/files/FilePreview'
import FileEditor from '../../components/files/FileEditor'
import { toast } from 'react-hot-toast'

const FilesPage: React.FC = () => {
  const {
    viewMode,
    currentPath,
    setFiles,
    setLoading,
    clearSelection,
    selectedFiles,
    files
  } = useFilesStore()

  const [showUploadZone, setShowUploadZone] = useState(false)
  const [previewFile, setPreviewFile] = useState<FileItem | null>(null)
  const [editFile, setEditFile] = useState<FileItem | null>(null)

  useEffect(() => {
    loadFiles()
  }, [currentPath])

  const loadFiles = async () => {
    setLoading(true)
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 500))
      
      // Mock data
      const mockFiles: FileItem[] = [
        {
          id: '1',
          name: 'Documents',
          type: 'folder',
          size: 0,
          lastModified: new Date('2024-01-15'),
          path: '/Documents',
          permissions: { read: true, write: true, execute: true },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '2',
          name: 'Images',
          type: 'folder',
          size: 0,
          lastModified: new Date('2024-01-10'),
          path: '/Images',
          permissions: { read: true, write: true, execute: true },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '3',
          name: 'config.json',
          type: 'file',
          size: 2048,
          lastModified: new Date('2024-01-20'),
          extension: 'json',
          mimeType: 'application/json',
          path: '/config.json',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '4',
          name: 'index.html',
          type: 'file',
          size: 4096,
          lastModified: new Date('2024-01-18'),
          extension: 'html',
          mimeType: 'text/html',
          path: '/index.html',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '5',
          name: 'styles.css',
          type: 'file',
          size: 1536,
          lastModified: new Date('2024-01-17'),
          extension: 'css',
          mimeType: 'text/css',
          path: '/styles.css',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '6',
          name: 'script.js',
          type: 'file',
          size: 3072,
          lastModified: new Date('2024-01-16'),
          extension: 'js',
          mimeType: 'application/javascript',
          path: '/script.js',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '7',
          name: 'README.md',
          type: 'file',
          size: 1024,
          lastModified: new Date('2024-01-14'),
          extension: 'md',
          mimeType: 'text/markdown',
          path: '/README.md',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        },
        {
          id: '8',
          name: 'logo.png',
          type: 'file',
          size: 15360,
          lastModified: new Date('2024-01-12'),
          extension: 'png',
          mimeType: 'image/png',
          path: '/logo.png',
          permissions: { read: true, write: true, execute: false },
          owner: 'admin',
          group: 'admin'
        }
      ]
      
      setFiles(mockFiles)
    } catch (error) {
      console.error('Error loading files:', error)
      toast.error('Erreur lors du chargement des fichiers')
    } finally {
      setLoading(false)
    }
  }

  const handleFileClick = (file: FileItem) => {
    if (file.type === 'folder') {
      // Navigate to folder
      // setCurrentPath(file.path)
      toast.info(`Navigation vers ${file.name}`)
    } else {
      // Preview file
      setPreviewFile(file)
    }
  }

  const handleFileAction = (action: string, file: FileItem) => {
    switch (action) {
      case 'preview':
        setPreviewFile(file)
        break
      case 'edit':
        setEditFile(file)
        break
      case 'download':
        toast.success(`Téléchargement de ${file.name}`)
        break
      case 'copy':
        toast.success(`${file.name} copié`)
        break
      case 'move':
        toast.info(`Déplacement de ${file.name}`)
        break
      case 'delete':
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${file.name} ?`)) {
          const newFiles = files.filter(f => f.id !== file.id)
          setFiles(newFiles)
          toast.success(`${file.name} supprimé`)
        }
        break
    }
  }

  const handleBulkAction = (action: string) => {
    const selectedCount = selectedFiles.length
    
    switch (action) {
      case 'download':
        toast.success(`Téléchargement de ${selectedCount} fichier(s)`)
        break
      case 'copy':
        toast.success(`${selectedCount} fichier(s) copié(s)`)
        break
      case 'move':
        toast.info(`Déplacement de ${selectedCount} fichier(s)`)
        break
      case 'delete':
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedCount} fichier(s) ?`)) {
          const newFiles = files.filter(f => !selectedFiles.includes(f.id))
          setFiles(newFiles)
          clearSelection()
          toast.success(`${selectedCount} fichier(s) supprimé(s)`)
        }
        break
    }
  }

  const handleUpload = (uploadedFiles: File[]) => {
    const newFiles: FileItem[] = uploadedFiles.map((file, index) => ({
      id: `upload-${Date.now()}-${index}`,
      name: generateUniqueFileName(file.name, files),
      type: 'file' as const,
      size: file.size,
      lastModified: new Date(),
      extension: file.name.split('.').pop(),
      mimeType: file.type,
      path: `/${file.name}`,
      permissions: { read: true, write: true, execute: false },
      owner: 'admin',
      group: 'admin'
    }))
    
    setFiles([...files, ...newFiles])
    toast.success(`${uploadedFiles.length} fichier(s) téléchargé(s)`)
  }

  const handleCreateFolder = () => {
    const folderName = prompt('Nom du nouveau dossier:')
    if (folderName) {
      const newFolder: FileItem = {
        id: `folder-${Date.now()}`,
        name: generateUniqueFileName(folderName, files),
        type: 'folder',
        size: 0,
        lastModified: new Date(),
        path: `/${folderName}`,
        permissions: { read: true, write: true, execute: true },
        owner: 'admin',
        group: 'admin'
      }
      
      setFiles([...files, newFolder])
      toast.success(`Dossier "${newFolder.name}" créé`)
    }
  }

  const handleSaveFile = (file: FileItem, content: string) => {
    toast.success(`${file.name} sauvegardé`)
    setEditFile(null)
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Gestionnaire de Fichiers</h1>
        <p className="text-gray-500 dark:text-gray-400">Explorez et gérez vos fichiers</p>
      </div>

      {/* Toolbar */}
      <FileToolbar
        onUpload={() => setShowUploadZone(true)}
        onCreateFolder={handleCreateFolder}
        onRefresh={loadFiles}
        onBulkAction={handleBulkAction}
      />

      {/* Upload Zone */}
      <AnimatePresence>
        {showUploadZone && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="overflow-hidden"
          >
            <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                  Télécharger des fichiers
                </h3>
                <button
                  onClick={() => setShowUploadZone(false)}
                  className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                  ✕
                </button>
              </div>
              <FileUploadZone onUpload={handleUpload} />
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* File Display */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="min-h-[400px]"
      >
        {viewMode === 'list' ? (
          <FileList
            onFileClick={handleFileClick}
            onFileAction={handleFileAction}
          />
        ) : (
          <FileGrid
            onFileClick={handleFileClick}
            onFileAction={handleFileAction}
          />
        )}
      </motion.div>

      {/* File Preview Modal */}
      <FilePreview
        file={previewFile}
        isOpen={!!previewFile}
        onClose={() => setPreviewFile(null)}
        onEdit={(file) => {
          setPreviewFile(null)
          setEditFile(file)
        }}
        onDownload={(file) => {
          toast.success(`Téléchargement de ${file.name}`)
        }}
      />

      {/* File Editor Modal */}
      <FileEditor
        file={editFile}
        isOpen={!!editFile}
        onClose={() => setEditFile(null)}
        onSave={handleSaveFile}
      />
    </div>
  )
}

export default FilesPage
