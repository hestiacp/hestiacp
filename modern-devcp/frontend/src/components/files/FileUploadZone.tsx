import React, { useCallback, useState } from 'react'
import { useDropzone } from 'react-dropzone'
import { motion, AnimatePresence } from 'framer-motion'
import { Upload, X, CheckCircle, AlertCircle } from 'lucide-react'
import { useFilesStore } from '../../stores/filesStore'
import { FileUpload } from '../../types/files'
import { formatFileSize } from '../../utils/fileUtils'

interface FileUploadZoneProps {
  onUpload?: (files: File[]) => void
}

const FileUploadZone: React.FC<FileUploadZoneProps> = ({ onUpload }) => {
  const { uploads, addUpload, updateUpload, removeUpload } = useFilesStore()
  const [isDragActive, setIsDragActive] = useState(false)

  const onDrop = useCallback((acceptedFiles: File[]) => {
    acceptedFiles.forEach(file => {
      const upload: FileUpload = {
        file,
        progress: 0,
        status: 'pending'
      }
      
      addUpload(upload)
      
      // Simulate upload progress
      simulateUpload(file.name)
    })
    
    onUpload?.(acceptedFiles)
  }, [addUpload, onUpload])

  const simulateUpload = (fileName: string) => {
    updateUpload(fileName, { status: 'uploading' })
    
    let progress = 0
    const interval = setInterval(() => {
      progress += Math.random() * 20
      
      if (progress >= 100) {
        progress = 100
        updateUpload(fileName, { progress: 100, status: 'completed' })
        clearInterval(interval)
        
        // Remove completed upload after 3 seconds
        setTimeout(() => {
          removeUpload(fileName)
        }, 3000)
      } else {
        updateUpload(fileName, { progress })
      }
    }, 200)
  }

  const { getRootProps, getInputProps, isDragActive: dropzoneActive } = useDropzone({
    onDrop,
    onDragEnter: () => setIsDragActive(true),
    onDragLeave: () => setIsDragActive(false),
    multiple: true
  })

  const getStatusIcon = (status: FileUpload['status']) => {
    switch (status) {
      case 'completed':
        return <CheckCircle className="w-4 h-4 text-green-500" />
      case 'error':
        return <AlertCircle className="w-4 h-4 text-red-500" />
      default:
        return <Upload className="w-4 h-4 text-blue-500" />
    }
  }

  const getStatusColor = (status: FileUpload['status']) => {
    switch (status) {
      case 'completed':
        return 'bg-green-500'
      case 'error':
        return 'bg-red-500'
      default:
        return 'bg-blue-500'
    }
  }

  return (
    <div className="space-y-4">
      {/* Upload Zone */}
      <motion.div
        {...getRootProps()}
        className={`
          relative border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-all duration-200
          ${isDragActive || dropzoneActive
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
            : 'border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500'
          }
        `}
        whileHover={{ scale: 1.01 }}
        whileTap={{ scale: 0.99 }}
      >
        <input {...getInputProps()} />
        
        <motion.div
          animate={{
            scale: isDragActive || dropzoneActive ? 1.1 : 1,
            rotate: isDragActive || dropzoneActive ? 5 : 0
          }}
          className="w-16 h-16 mx-auto mb-4 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center"
        >
          <Upload className="w-8 h-8 text-primary-600 dark:text-primary-400" />
        </motion.div>
        
        <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
          {isDragActive || dropzoneActive ? 'Déposez vos fichiers ici' : 'Glissez-déposez vos fichiers'}
        </h3>
        
        <p className="text-gray-500 dark:text-gray-400 mb-4">
          ou cliquez pour sélectionner des fichiers
        </p>
        
        <div className="text-xs text-gray-400 dark:text-gray-500">
          Formats supportés : tous types de fichiers • Taille max : 100MB par fichier
        </div>
      </motion.div>

      {/* Upload Progress */}
      <AnimatePresence>
        {uploads.length > 0 && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="space-y-2"
          >
            <h4 className="text-sm font-medium text-gray-900 dark:text-white">
              Téléchargements en cours ({uploads.length})
            </h4>
            
            {uploads.map((upload) => (
              <motion.div
                key={upload.file.name}
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 20 }}
                className="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700"
              >
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center space-x-2">
                    {getStatusIcon(upload.status)}
                    <span className="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {upload.file.name}
                    </span>
                    <span className="text-xs text-gray-500">
                      {formatFileSize(upload.file.size)}
                    </span>
                  </div>
                  
                  <button
                    onClick={() => removeUpload(upload.file.name)}
                    className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                  >
                    <X className="w-4 h-4" />
                  </button>
                </div>
                
                {upload.status === 'uploading' && (
                  <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <motion.div
                      className={`h-2 rounded-full ${getStatusColor(upload.status)}`}
                      initial={{ width: 0 }}
                      animate={{ width: `${upload.progress}%` }}
                      transition={{ duration: 0.3 }}
                    />
                  </div>
                )}
                
                {upload.status === 'error' && upload.error && (
                  <p className="text-xs text-red-500 mt-1">{upload.error}</p>
                )}
                
                {upload.status === 'completed' && (
                  <p className="text-xs text-green-500 mt-1">Téléchargement terminé</p>
                )}
              </motion.div>
            ))}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

export default FileUploadZone