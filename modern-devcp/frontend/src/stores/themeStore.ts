import { create } from 'zustand'
import { persist } from 'zustand/middleware'

type Theme = 'light' | 'dark' | 'system'

interface ThemeState {
  theme: Theme
  actualTheme: 'light' | 'dark'
  
  // Actions
  setTheme: (theme: Theme) => void
  toggleTheme: () => void
}

// Helper function to get system theme
const getSystemTheme = (): 'light' | 'dark' => {
  if (typeof window !== 'undefined') {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  return 'light'
}

// Helper function to get actual theme based on preference
const getActualTheme = (theme: Theme): 'light' | 'dark' => {
  if (theme === 'system') {
    return getSystemTheme()
  }
  return theme
}

export const useThemeStore = create<ThemeState>()(
  persist(
    (set, get) => ({
      theme: 'system',
      actualTheme: getSystemTheme(),

      setTheme: (theme: Theme) => {
        const actualTheme = getActualTheme(theme)
        set({ theme, actualTheme })
        
        // Apply theme to document
        if (actualTheme === 'dark') {
          document.documentElement.classList.add('dark')
        } else {
          document.documentElement.classList.remove('dark')
        }
      },

      toggleTheme: () => {
        const { theme } = get()
        const newTheme = theme === 'dark' ? 'light' : 'dark'
        get().setTheme(newTheme)
      },
    }),
    {
      name: 'devcp-theme',
      partialize: (state) => ({
        theme: state.theme,
      }),
      onRehydrateStorage: () => (state) => {
        if (state) {
          // Update actual theme on rehydration
          const actualTheme = getActualTheme(state.theme)
          state.actualTheme = actualTheme
          
          // Apply theme to document
          if (actualTheme === 'dark') {
            document.documentElement.classList.add('dark')
          } else {
            document.documentElement.classList.remove('dark')
          }
        }
      },
    }
  )
)

// Listen for system theme changes
if (typeof window !== 'undefined') {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    const { theme, setTheme } = useThemeStore.getState()
    if (theme === 'system') {
      setTheme('system') // This will trigger the theme update
    }
  })
}