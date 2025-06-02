import { create } from 'zustand'
import { devtools } from 'zustand/middleware'
import { FilesState, FileItem, FileUpload, ViewMode, SortField, SortOrder } from '../types/files'

interface FilesStore extends FilesState {
  // Actions
  setCurrentPath: (path: string) => void
  setFiles: (files: FileItem[]) => void
  setSelectedFiles: (files: string[]) => void
  toggleFileSelection: (fileId: string) => void
  selectAllFiles: () => void
  clearSelection: () => void
  setViewMode: (mode: ViewMode) => void
  setSorting: (field: SortField, order: SortOrder) => void
  setSearchQuery: (query: string) => void
  addUpload: (upload: FileUpload) => void
  updateUpload: (fileId: string, updates: Partial<FileUpload>) => void
  removeUpload: (fileId: string) => void
  setLoading: (loading: boolean) => void
  setError: (error: string | null) => void
  
  // Computed
  filteredFiles: () => FileItem[]
  sortedFiles: () => FileItem[]
  breadcrumbs: () => Array<{ name: string; path: string }>
}

export const useFilesStore = create<FilesStore>()(
  devtools(
    (set, get) => ({
      // Initial state
      currentPath: '/',
      files: [],
      selectedFiles: [],
      viewMode: 'list',
      sortField: 'name',
      sortOrder: 'asc',
      searchQuery: '',
      uploads: [],
      loading: false,
      error: null,

      // Actions
      setCurrentPath: (path) => set({ currentPath: path }),
      
      setFiles: (files) => set({ files }),
      
      setSelectedFiles: (files) => set({ selectedFiles: files }),
      
      toggleFileSelection: (fileId) => set((state) => ({
        selectedFiles: state.selectedFiles.includes(fileId)
          ? state.selectedFiles.filter(id => id !== fileId)
          : [...state.selectedFiles, fileId]
      })),
      
      selectAllFiles: () => set((state) => ({
        selectedFiles: state.files.map(file => file.id)
      })),
      
      clearSelection: () => set({ selectedFiles: [] }),
      
      setViewMode: (mode) => set({ viewMode: mode }),
      
      setSorting: (field, order) => set({ sortField: field, sortOrder: order }),
      
      setSearchQuery: (query) => set({ searchQuery: query }),
      
      addUpload: (upload) => set((state) => ({
        uploads: [...state.uploads, upload]
      })),
      
      updateUpload: (fileId, updates) => set((state) => ({
        uploads: state.uploads.map(upload =>
          upload.file.name === fileId ? { ...upload, ...updates } : upload
        )
      })),
      
      removeUpload: (fileId) => set((state) => ({
        uploads: state.uploads.filter(upload => upload.file.name !== fileId)
      })),
      
      setLoading: (loading) => set({ loading }),
      
      setError: (error) => set({ error }),

      // Computed
      filteredFiles: () => {
        const { files, searchQuery } = get()
        if (!searchQuery) return files
        
        return files.filter(file =>
          file.name.toLowerCase().includes(searchQuery.toLowerCase())
        )
      },
      
      sortedFiles: () => {
        const { sortField, sortOrder } = get()
        const filtered = get().filteredFiles()
        
        return [...filtered].sort((a, b) => {
          let aValue: any = a[sortField]
          let bValue: any = b[sortField]
          
          if (sortField === 'lastModified') {
            aValue = new Date(aValue).getTime()
            bValue = new Date(bValue).getTime()
          }
          
          if (typeof aValue === 'string') {
            aValue = aValue.toLowerCase()
            bValue = bValue.toLowerCase()
          }
          
          const result = aValue < bValue ? -1 : aValue > bValue ? 1 : 0
          return sortOrder === 'asc' ? result : -result
        })
      },
      
      breadcrumbs: () => {
        const { currentPath } = get()
        const parts = currentPath.split('/').filter(Boolean)
        const breadcrumbs = [{ name: 'Racine', path: '/' }]
        
        let currentPathBuild = ''
        parts.forEach(part => {
          currentPathBuild += `/${part}`
          breadcrumbs.push({
            name: part,
            path: currentPathBuild
          })
        })
        
        return breadcrumbs
      }
    }),
    {
      name: 'files-store'
    }
  )
)