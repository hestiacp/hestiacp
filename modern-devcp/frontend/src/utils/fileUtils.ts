import { FileItem } from '../types/files'

export const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 B'
  
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`
}

export const formatDate = (date: Date): string => {
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days === 0) {
    return 'Aujourd\'hui'
  } else if (days === 1) {
    return 'Hier'
  } else if (days < 7) {
    return `Il y a ${days} jours`
  } else {
    return date.toLocaleDateString('fr-FR')
  }
}

export const getFileIcon = (file: FileItem): string => {
  if (file.type === 'folder') return '📁'
  
  const ext = file.extension?.toLowerCase()
  
  const iconMap: Record<string, string> = {
    // Images
    'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️', 'svg': '🖼️', 'webp': '🖼️',
    // Documents
    'pdf': '📄', 'doc': '📝', 'docx': '📝', 'txt': '📄', 'rtf': '📄',
    // Spreadsheets
    'xls': '📊', 'xlsx': '📊', 'csv': '📊',
    // Presentations
    'ppt': '📊', 'pptx': '📊',
    // Code
    'js': '📜', 'ts': '📜', 'jsx': '📜', 'tsx': '📜', 'html': '🌐', 'css': '🎨', 'scss': '🎨',
    'php': '🐘', 'py': '🐍', 'java': '☕', 'cpp': '⚙️', 'c': '⚙️', 'go': '🐹',
    'json': '📋', 'xml': '📋', 'yaml': '📋', 'yml': '📋',
    // Archives
    'zip': '📦', 'rar': '📦', 'tar': '📦', 'gz': '📦', '7z': '📦',
    // Media
    'mp4': '🎬', 'avi': '🎬', 'mov': '🎬', 'wmv': '🎬', 'flv': '🎬',
    'mp3': '🎵', 'wav': '🎵', 'flac': '🎵', 'aac': '🎵',
    // Others
    'exe': '⚙️', 'msi': '⚙️', 'deb': '📦', 'rpm': '📦',
    'sql': '🗄️', 'db': '🗄️', 'sqlite': '🗄️'
  }
  
  return iconMap[ext || ''] || '📄'
}

export const isImageFile = (file: FileItem): boolean => {
  const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp']
  return imageExtensions.includes(file.extension?.toLowerCase() || '')
}

export const isTextFile = (file: FileItem): boolean => {
  const textExtensions = [
    'txt', 'md', 'json', 'xml', 'html', 'htm', 'css', 'scss', 'sass', 'js', 'ts', 'jsx', 'tsx', 
    'php', 'py', 'java', 'cpp', 'c', 'go', 'yaml', 'yml', 'sql', 'rb', 'h', 'hpp', 'cs', 
    'sh', 'bash', 'zsh', 'ini', 'conf', 'config', 'env', 'log'
  ]
  return textExtensions.includes(file.extension?.toLowerCase() || '')
}

export const isEditableFile = (file: FileItem): boolean => {
  return isTextFile(file) && file.size < 1024 * 1024 // Max 1MB for editing
}

export const getFileTypeColor = (file: FileItem): string => {
  if (file.type === 'folder') return 'text-blue-600 dark:text-blue-400'
  
  const ext = file.extension?.toLowerCase()
  
  const colorMap: Record<string, string> = {
    // Images
    'jpg': 'text-green-600 dark:text-green-400',
    'jpeg': 'text-green-600 dark:text-green-400',
    'png': 'text-green-600 dark:text-green-400',
    'gif': 'text-green-600 dark:text-green-400',
    'svg': 'text-green-600 dark:text-green-400',
    // Code
    'js': 'text-yellow-600 dark:text-yellow-400',
    'ts': 'text-blue-600 dark:text-blue-400',
    'html': 'text-orange-600 dark:text-orange-400',
    'css': 'text-purple-600 dark:text-purple-400',
    'php': 'text-indigo-600 dark:text-indigo-400',
    'py': 'text-green-600 dark:text-green-400',
    // Documents
    'pdf': 'text-red-600 dark:text-red-400',
    'doc': 'text-blue-600 dark:text-blue-400',
    'docx': 'text-blue-600 dark:text-blue-400',
    // Archives
    'zip': 'text-gray-600 dark:text-gray-400',
    'rar': 'text-gray-600 dark:text-gray-400'
  }
  
  return colorMap[ext || ''] || 'text-gray-600 dark:text-gray-400'
}

export const validateFileName = (name: string): string | null => {
  if (!name.trim()) return 'Le nom ne peut pas être vide'
  if (name.length > 255) return 'Le nom est trop long (max 255 caractères)'
  if (/[<>:"/\\|?*]/.test(name)) return 'Le nom contient des caractères interdits'
  if (name.startsWith('.') && name.length === 1) return 'Nom invalide'
  return null
}

export const generateUniqueFileName = (originalName: string, existingFiles: FileItem[]): string => {
  const existingNames = existingFiles.map(f => f.name)
  
  if (!existingNames.includes(originalName)) {
    return originalName
  }
  
  const ext = originalName.includes('.') ? originalName.split('.').pop() : ''
  const nameWithoutExt = ext ? originalName.slice(0, -(ext.length + 1)) : originalName
  
  let counter = 1
  let newName = ext ? `${nameWithoutExt} (${counter}).${ext}` : `${nameWithoutExt} (${counter})`
  
  while (existingNames.includes(newName)) {
    counter++
    newName = ext ? `${nameWithoutExt} (${counter}).${ext}` : `${nameWithoutExt} (${counter})`
  }
  
  return newName
}



export const isArchiveFile = (file: FileItem): boolean => {
  const archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz']
  return archiveExtensions.includes(file.extension?.toLowerCase() || '')
}

export const isAudioFile = (file: FileItem): boolean => {
  const audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a']
  return audioExtensions.includes(file.extension?.toLowerCase() || '')
}

export const isVideoFile = (file: FileItem): boolean => {
  const videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv']
  return videoExtensions.includes(file.extension?.toLowerCase() || '')
}

export const isDocumentFile = (file: FileItem): boolean => {
  const documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp']
  return documentExtensions.includes(file.extension?.toLowerCase() || '')
}