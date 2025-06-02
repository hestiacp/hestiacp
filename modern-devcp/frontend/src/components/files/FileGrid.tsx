import React from 'react'
import { motion } from 'framer-motion'
import { MoreVertical, Download, Edit, Trash2, Copy, Move, Eye } from 'lucide-react'
import { useFilesStore } from '../../stores/filesStore'
import { FileItem } from '../../types/files'
import { formatFileSize, formatDate, getFileIcon, getFileTypeColor, isImageFile } from '../../utils/fileUtils'

interface FileGridProps {
  onFileClick?: (file: FileItem) => void
  onFileAction?: (action: string, file: FileItem) => void
}

const FileGrid: React.FC<FileGridProps> = ({ onFileClick, onFileAction }) => {
  const { sortedFiles, selectedFiles, toggleFileSelection } = useFilesStore()
  const files = sortedFiles()

  const FileCard = ({ file, index }: { file: FileItem; index: number }) => {
    const isSelected = selectedFiles.includes(file.id)
    
    return (
      <motion.div
        initial={{ opacity: 0, scale: 0.9 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ delay: index * 0.05 }}
        className={`
          group relative bg-white dark:bg-gray-800 rounded-lg border-2 transition-all duration-200 cursor-pointer
          ${isSelected 
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' 
            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
          }
        `}
        onClick={() => onFileClick?.(file)}
        whileHover={{ y: -2 }}
      >
        {/* Selection Checkbox */}
        <div className="absolute top-2 left-2 z-10">
          <input
            type="checkbox"
            checked={isSelected}
            onChange={(e) => {
              e.stopPropagation()
              toggleFileSelection(file.id)
            }}
            className="rounded border-gray-300 dark:border-gray-600"
          />
        </div>

        {/* Actions Menu */}
        <div className="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
          <div className="relative group/menu">
            <button
              onClick={(e) => e.stopPropagation()}
              className="p-1 bg-white dark:bg-gray-800 rounded-full shadow-md border border-gray-200 dark:border-gray-600 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <MoreVertical className="w-4 h-4" />
            </button>
            
            <div className="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover/menu:opacity-100 group-hover/menu:visible transition-all">
              <div className="py-1">
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('preview', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
                >
                  <Eye className="w-4 h-4" />
                  <span>Aperçu</span>
                </button>
                
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('edit', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
                >
                  <Edit className="w-4 h-4" />
                  <span>Éditer</span>
                </button>
                
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('download', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
                >
                  <Download className="w-4 h-4" />
                  <span>Télécharger</span>
                </button>
                
                <hr className="my-1 border-gray-200 dark:border-gray-600" />
                
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('copy', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
                >
                  <Copy className="w-4 h-4" />
                  <span>Copier</span>
                </button>
                
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('move', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2"
                >
                  <Move className="w-4 h-4" />
                  <span>Déplacer</span>
                </button>
                
                <hr className="my-1 border-gray-200 dark:border-gray-600" />
                
                <button
                  onClick={(e) => {
                    e.stopPropagation()
                    onFileAction?.('delete', file)
                  }}
                  className="w-full px-3 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center space-x-2"
                >
                  <Trash2 className="w-4 h-4" />
                  <span>Supprimer</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* File Preview */}
        <div className="p-4">
          <div className="aspect-square mb-3 flex items-center justify-center bg-gray-50 dark:bg-gray-700 rounded-lg">
            {isImageFile(file) ? (
              <div className="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-2xl">🖼️</span>
              </div>
            ) : (
              <span className="text-4xl">{getFileIcon(file)}</span>
            )}
          </div>
          
          <div className="space-y-1">
            <h3 className={`font-medium text-sm truncate ${getFileTypeColor(file)}`} title={file.name}>
              {file.name}
            </h3>
            
            <div className="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
              <span>{file.type === 'folder' ? 'Dossier' : formatFileSize(file.size)}</span>
              {file.extension && (
                <span className="uppercase font-medium">{file.extension}</span>
              )}
            </div>
            
            <p className="text-xs text-gray-400 dark:text-gray-500">
              {formatDate(file.lastModified)}
            </p>
          </div>
        </div>

        {/* Selection Overlay */}
        {isSelected && (
          <div className="absolute inset-0 bg-primary-500/10 rounded-lg pointer-events-none" />
        )}
      </motion.div>
    )
  }

  return (
    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
      {files.length === 0 ? (
        <div className="col-span-full flex flex-col items-center justify-center py-12">
          <div className="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
            <span className="text-3xl">📁</span>
          </div>
          <p className="text-gray-500 dark:text-gray-400">Aucun fichier dans ce dossier</p>
        </div>
      ) : (
        files.map((file, index) => (
          <FileCard key={file.id} file={file} index={index} />
        ))
      )}
    </div>
  )
}

export default FileGrid