export interface FileItem {
  id: string
  name: string
  type: 'file' | 'folder'
  size: number
  lastModified: Date
  extension?: string
  mimeType?: string
  path: string
  permissions: {
    read: boolean
    write: boolean
    execute: boolean
  }
  owner: string
  group: string
}

export interface FileUpload {
  file: File
  progress: number
  status: 'pending' | 'uploading' | 'completed' | 'error'
  error?: string
}

export interface FileAction {
  id: string
  label: string
  icon: React.ComponentType<any>
  action: (files: FileItem[]) => void
  disabled?: boolean
  destructive?: boolean
}

export interface BreadcrumbItem {
  name: string
  path: string
}

export type ViewMode = 'list' | 'grid'
export type SortField = 'name' | 'size' | 'lastModified' | 'type'
export type SortOrder = 'asc' | 'desc'

export interface FilesState {
  currentPath: string
  files: FileItem[]
  selectedFiles: string[]
  viewMode: ViewMode
  sortField: SortField
  sortOrder: SortOrder
  searchQuery: string
  uploads: FileUpload[]
  loading: boolean
  error: string | null
}