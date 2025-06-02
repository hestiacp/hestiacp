import React from 'react'
import { motion } from 'framer-motion'
import { 
  ChevronDown, 
  ChevronUp, 
  MoreVertical, 
  Download, 
  Edit, 
  Trash2, 
  Copy,
  Move,
  Eye
} from 'lucide-react'
import { useFilesStore } from '../../stores/filesStore'
import { FileItem, SortField, SortOrder } from '../../types/files'
import { formatFileSize, formatDate, getFileIcon, getFileTypeColor } from '../../utils/fileUtils'

interface FileListProps {
  onFileClick?: (file: FileItem) => void
  onFileAction?: (action: string, file: FileItem) => void
}

const FileList: React.FC<FileListProps> = ({ onFileClick, onFileAction }) => {
  const {
    sortedFiles,
    selectedFiles,
    sortField,
    sortOrder,
    toggleFileSelection,
    selectAllFiles,
    clearSelection,
    setSorting
  } = useFilesStore()

  const files = sortedFiles()

  const handleSort = (field: SortField) => {
    const newOrder: SortOrder = sortField === field && sortOrder === 'asc' ? 'desc' : 'asc'
    setSorting(field, newOrder)
  }

  const handleSelectAll = () => {
    if (selectedFiles.length === files.length) {
      clearSelection()
    } else {
      selectAllFiles()
    }
  }

  const SortIcon = ({ field }: { field: SortField }) => {
    if (sortField !== field) return null
    return sortOrder === 'asc' ? 
      <ChevronUp className="w-4 h-4" /> : 
      <ChevronDown className="w-4 h-4" />
  }

  const FileActions = ({ file }: { file: FileItem }) => (
    <div className="opacity-0 group-hover:opacity-100 transition-opacity">
      <div className="flex items-center space-x-1">
        <button
          onClick={(e) => {
            e.stopPropagation()
            onFileAction?.('preview', file)
          }}
          className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          title="Aperçu"
        >
          <Eye className="w-4 h-4" />
        </button>
        
        <button
          onClick={(e) => {
            e.stopPropagation()
            onFileAction?.('edit', file)
          }}
          className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          title="Éditer"
        >
          <Edit className="w-4 h-4" />
        </button>
        
        <button
          onClick={(e) => {
            e.stopPropagation()
            onFileAction?.('download', file)
          }}
          className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          title="Télécharger"
        >
          <Download className="w-4 h-4" />
        </button>
        
        <div className="relative group/menu">
          <button
            onClick={(e) => e.stopPropagation()}
            className="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
          >
            <MoreVertical className="w-4 h-4" />
          </button>
          
          <div className="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 opacity-0 invisible group-hover/menu:opacity-100 group-hover/menu:visible transition-all z-10">
            <div className="py-1">
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
    </div>
  )

  return (
    <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
      {/* Header */}
      <div className="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
        <div className="grid grid-cols-12 gap-4 items-center text-sm font-medium text-gray-700 dark:text-gray-300">
          <div className="col-span-1">
            <input
              type="checkbox"
              checked={selectedFiles.length === files.length && files.length > 0}
              onChange={handleSelectAll}
              className="rounded border-gray-300 dark:border-gray-600"
            />
          </div>
          
          <div className="col-span-5">
            <button
              onClick={() => handleSort('name')}
              className="flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white"
            >
              <span>Nom</span>
              <SortIcon field="name" />
            </button>
          </div>
          
          <div className="col-span-2">
            <button
              onClick={() => handleSort('size')}
              className="flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white"
            >
              <span>Taille</span>
              <SortIcon field="size" />
            </button>
          </div>
          
          <div className="col-span-3">
            <button
              onClick={() => handleSort('lastModified')}
              className="flex items-center space-x-1 hover:text-gray-900 dark:hover:text-white"
            >
              <span>Modifié</span>
              <SortIcon field="lastModified" />
            </button>
          </div>
          
          <div className="col-span-1">
            <span>Actions</span>
          </div>
        </div>
      </div>

      {/* File List */}
      <div className="divide-y divide-gray-200 dark:divide-gray-700">
        {files.length === 0 ? (
          <div className="p-8 text-center text-gray-500 dark:text-gray-400">
            <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
              📁
            </div>
            <p>Aucun fichier dans ce dossier</p>
          </div>
        ) : (
          files.map((file, index) => (
            <motion.div
              key={file.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className={`
                group grid grid-cols-12 gap-4 items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors
                ${selectedFiles.includes(file.id) ? 'bg-primary-50 dark:bg-primary-900/20' : ''}
              `}
              onClick={() => onFileClick?.(file)}
            >
              <div className="col-span-1">
                <input
                  type="checkbox"
                  checked={selectedFiles.includes(file.id)}
                  onChange={(e) => {
                    e.stopPropagation()
                    toggleFileSelection(file.id)
                  }}
                  className="rounded border-gray-300 dark:border-gray-600"
                />
              </div>
              
              <div className="col-span-5 flex items-center space-x-3">
                <span className="text-2xl">{getFileIcon(file)}</span>
                <div className="min-w-0 flex-1">
                  <p className={`text-sm font-medium truncate ${getFileTypeColor(file)}`}>
                    {file.name}
                  </p>
                  {file.extension && (
                    <p className="text-xs text-gray-500 dark:text-gray-400 uppercase">
                      {file.extension}
                    </p>
                  )}
                </div>
              </div>
              
              <div className="col-span-2">
                <span className="text-sm text-gray-500 dark:text-gray-400">
                  {file.type === 'folder' ? '—' : formatFileSize(file.size)}
                </span>
              </div>
              
              <div className="col-span-3">
                <span className="text-sm text-gray-500 dark:text-gray-400">
                  {formatDate(file.lastModified)}
                </span>
              </div>
              
              <div className="col-span-1">
                <FileActions file={file} />
              </div>
            </motion.div>
          ))
        )}
      </div>
    </div>
  )
}

export default FileList