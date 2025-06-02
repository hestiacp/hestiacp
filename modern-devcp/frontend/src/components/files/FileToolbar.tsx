import React, { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { 
  Search, 
  Grid3X3, 
  List, 
  Upload, 
  FolderPlus, 
  Download, 
  Trash2, 
  Copy, 
  Move,
  Filter,
  SortAsc,
  SortDesc,
  RefreshCw,
  Home,
  ChevronRight
} from 'lucide-react'
import { useFilesStore } from '../../stores/filesStore'
import { ViewMode, SortField, SortOrder } from '../../types/files'

interface FileToolbarProps {
  onUpload?: () => void
  onCreateFolder?: () => void
  onRefresh?: () => void
  onBulkAction?: (action: string) => void
}

const FileToolbar: React.FC<FileToolbarProps> = ({
  onUpload,
  onCreateFolder,
  onRefresh,
  onBulkAction
}) => {
  const {
    viewMode,
    setViewMode,
    searchQuery,
    setSearchQuery,
    selectedFiles,
    sortField,
    sortOrder,
    setSorting,
    currentPath,
    setCurrentPath,
    breadcrumbs
  } = useFilesStore()

  const [showSortMenu, setShowSortMenu] = useState(false)
  const [showFilterMenu, setShowFilterMenu] = useState(false)

  const handleSort = (field: SortField) => {
    const newOrder: SortOrder = sortField === field && sortOrder === 'asc' ? 'desc' : 'asc'
    setSorting(field, newOrder)
    setShowSortMenu(false)
  }

  const breadcrumbItems = breadcrumbs()

  return (
    <div className="space-y-4">
      {/* Breadcrumb Navigation */}
      <nav className="flex items-center space-x-2 text-sm">
        <button
          onClick={() => setCurrentPath('/')}
          className="flex items-center space-x-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          <Home className="w-4 h-4" />
          <span>Accueil</span>
        </button>
        
        {breadcrumbItems.slice(1).map((item, index) => (
          <React.Fragment key={item.path}>
            <ChevronRight className="w-4 h-4 text-gray-400" />
            <button
              onClick={() => setCurrentPath(item.path)}
              className={`hover:text-gray-700 dark:hover:text-gray-200 ${
                index === breadcrumbItems.length - 2
                  ? 'text-gray-900 dark:text-white font-medium'
                  : 'text-gray-500 dark:text-gray-400'
              }`}
            >
              {item.name}
            </button>
          </React.Fragment>
        ))}
      </nav>

      {/* Main Toolbar */}
      <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        {/* Left Section - Search */}
        <div className="flex-1 max-w-md">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              type="text"
              placeholder="Rechercher des fichiers..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>
        </div>

        {/* Right Section - Actions */}
        <div className="flex items-center space-x-2">
          {/* View Mode Toggle */}
          <div className="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
            <button
              onClick={() => setViewMode('list')}
              className={`p-2 rounded ${
                viewMode === 'list'
                  ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
              }`}
              title="Vue liste"
            >
              <List className="w-4 h-4" />
            </button>
            <button
              onClick={() => setViewMode('grid')}
              className={`p-2 rounded ${
                viewMode === 'grid'
                  ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'
              }`}
              title="Vue grille"
            >
              <Grid3X3 className="w-4 h-4" />
            </button>
          </div>

          {/* Sort Menu */}
          <div className="relative">
            <button
              onClick={() => setShowSortMenu(!showSortMenu)}
              className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
              title="Trier"
            >
              {sortOrder === 'asc' ? <SortAsc className="w-4 h-4" /> : <SortDesc className="w-4 h-4" />}
            </button>
            
            <AnimatePresence>
              {showSortMenu && (
                <motion.div
                  initial={{ opacity: 0, y: -10 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -10 }}
                  className="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
                >
                  <div className="py-1">
                    <button
                      onClick={() => handleSort('name')}
                      className={`w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 ${
                        sortField === 'name' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'
                      }`}
                    >
                      Nom
                    </button>
                    <button
                      onClick={() => handleSort('size')}
                      className={`w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 ${
                        sortField === 'size' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'
                      }`}
                    >
                      Taille
                    </button>
                    <button
                      onClick={() => handleSort('lastModified')}
                      className={`w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 ${
                        sortField === 'lastModified' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'
                      }`}
                    >
                      Date de modification
                    </button>
                    <button
                      onClick={() => handleSort('type')}
                      className={`w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 ${
                        sortField === 'type' ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'
                      }`}
                    >
                      Type
                    </button>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>
          </div>

          {/* Refresh Button */}
          <button
            onClick={onRefresh}
            className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
            title="Actualiser"
          >
            <RefreshCw className="w-4 h-4" />
          </button>

          {/* Upload Button */}
          <button
            onClick={onUpload}
            className="flex items-center space-x-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
          >
            <Upload className="w-4 h-4" />
            <span className="hidden sm:inline">Télécharger</span>
          </button>

          {/* Create Folder Button */}
          <button
            onClick={onCreateFolder}
            className="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
            title="Nouveau dossier"
          >
            <FolderPlus className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Bulk Actions Bar */}
      <AnimatePresence>
        {selectedFiles.length > 0 && (
          <motion.div
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            className="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg"
          >
            <span className="text-sm font-medium text-primary-700 dark:text-primary-300">
              {selectedFiles.length} élément(s) sélectionné(s)
            </span>
            
            <div className="flex items-center space-x-2">
              <button
                onClick={() => onBulkAction?.('download')}
                className="flex items-center space-x-1 px-3 py-1 text-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 rounded border border-gray-300 dark:border-gray-600"
              >
                <Download className="w-4 h-4" />
                <span>Télécharger</span>
              </button>
              
              <button
                onClick={() => onBulkAction?.('copy')}
                className="flex items-center space-x-1 px-3 py-1 text-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 rounded border border-gray-300 dark:border-gray-600"
              >
                <Copy className="w-4 h-4" />
                <span>Copier</span>
              </button>
              
              <button
                onClick={() => onBulkAction?.('move')}
                className="flex items-center space-x-1 px-3 py-1 text-sm text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 rounded border border-gray-300 dark:border-gray-600"
              >
                <Move className="w-4 h-4" />
                <span>Déplacer</span>
              </button>
              
              <button
                onClick={() => onBulkAction?.('delete')}
                className="flex items-center space-x-1 px-3 py-1 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded border border-red-300 dark:border-red-600"
              >
                <Trash2 className="w-4 h-4" />
                <span>Supprimer</span>
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

export default FileToolbar