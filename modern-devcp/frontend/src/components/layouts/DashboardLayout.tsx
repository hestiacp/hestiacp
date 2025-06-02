import React, { useState } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { 
  Bars3Icon, 
  XMarkIcon,
  HomeIcon,
  UsersIcon,
  GlobeAltIcon,
  CircleStackIcon,
  AtSymbolIcon,
  DocumentIcon,
  CloudArrowUpIcon,
  Cog6ToothIcon,
  SunIcon,
  MoonIcon,
  ComputerDesktopIcon,
  ArrowRightOnRectangleIcon,
  BellIcon,
  UserCircleIcon
} from '@heroicons/react/24/outline'
import { useLocation, Link } from 'react-router-dom'
import { useAuthStore } from '../../stores/authStore'
import { useThemeStore } from '../../stores/themeStore'

interface DashboardLayoutProps {
  children: React.ReactNode
}

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'Utilisateurs', href: '/users', icon: UsersIcon },
  { name: 'Sites Web', href: '/websites', icon: GlobeAltIcon },
  { name: 'Bases de données', href: '/databases', icon: CircleStackIcon },
  { name: 'DNS', href: '/dns', icon: AtSymbolIcon },
  { name: 'Mail', href: '/mail', icon: DocumentIcon },
  { name: 'Fichiers', href: '/files', icon: DocumentIcon },
  { name: 'Sauvegardes', href: '/backups', icon: CloudArrowUpIcon },
  { name: 'Paramètres', href: '/settings', icon: Cog6ToothIcon },
]

const DashboardLayout: React.FC<DashboardLayoutProps> = ({ children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const location = useLocation()
  const { user, logout } = useAuthStore()
  const { theme, setTheme } = useThemeStore()

  const handleLogout = () => {
    logout()
  }

  const themeOptions = [
    { value: 'light', label: 'Clair', icon: SunIcon },
    { value: 'dark', label: 'Sombre', icon: MoonIcon },
    { value: 'system', label: 'Système', icon: ComputerDesktopIcon },
  ]

  return (
    <div className="h-screen flex overflow-hidden bg-gray-50 dark:bg-gray-900">
      {/* Mobile sidebar */}
      <AnimatePresence>
        {sidebarOpen && (
          <>
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 flex z-40 md:hidden"
            >
              <div
                className="fixed inset-0 bg-gray-600 bg-opacity-75"
                onClick={() => setSidebarOpen(false)}
              />
              <motion.div
                initial={{ x: -300 }}
                animate={{ x: 0 }}
                exit={{ x: -300 }}
                transition={{ type: 'spring', damping: 25, stiffness: 200 }}
                className="relative flex-1 flex flex-col max-w-xs w-full bg-white dark:bg-gray-800 shadow-xl"
              >
                <div className="absolute top-0 right-0 -mr-12 pt-2">
                  <button
                    type="button"
                    className="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    onClick={() => setSidebarOpen(false)}
                  >
                    <XMarkIcon className="h-6 w-6 text-white" />
                  </button>
                </div>
                <SidebarContent navigation={navigation} currentPath={location.pathname} />
              </motion.div>
            </motion.div>
          </>
        )}
      </AnimatePresence>

      {/* Desktop sidebar */}
      <div className="hidden md:flex md:flex-shrink-0">
        <div className="flex flex-col w-64">
          <SidebarContent navigation={navigation} currentPath={location.pathname} />
        </div>
      </div>

      {/* Main content */}
      <div className="flex flex-col w-0 flex-1 overflow-hidden">
        {/* Top bar */}
        <div className="relative z-10 flex-shrink-0 flex h-16 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
          <button
            type="button"
            className="px-4 border-r border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 md:hidden"
            onClick={() => setSidebarOpen(true)}
          >
            <Bars3Icon className="h-6 w-6" />
          </button>

          <div className="flex-1 px-4 flex justify-between items-center">
            <div className="flex-1 flex">
              <h1 className="text-lg font-semibold text-gray-900 dark:text-white">
                {navigation.find(item => item.href === location.pathname)?.name || 'Dashboard'}
              </h1>
            </div>

            <div className="ml-4 flex items-center md:ml-6 space-x-3">
              {/* Theme selector */}
              <div className="relative">
                <select
                  value={theme}
                  onChange={(e) => setTheme(e.target.value as any)}
                  className="appearance-none bg-transparent border-none text-sm text-gray-500 dark:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg px-2 py-1"
                >
                  {themeOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>

              {/* Notifications */}
              <button className="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
                <BellIcon className="h-5 w-5" />
              </button>

              {/* Profile dropdown */}
              <div className="relative">
                <button className="flex items-center space-x-2 p-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
                  <UserCircleIcon className="h-6 w-6" />
                  <span className="text-sm font-medium">{user?.username}</span>
                </button>
              </div>

              {/* Logout */}
              <button
                onClick={handleLogout}
                className="p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg"
                title="Se déconnecter"
              >
                <ArrowRightOnRectangleIcon className="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>

        {/* Page content */}
        <main className="flex-1 relative overflow-y-auto focus:outline-none">
          <div className="py-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.3 }}
              >
                {children}
              </motion.div>
            </div>
          </div>
        </main>
      </div>
    </div>
  )
}

// Sidebar content component
interface SidebarContentProps {
  navigation: Array<{
    name: string
    href: string
    icon: React.ComponentType<{ className?: string }>
  }>
  currentPath: string
}

const SidebarContent: React.FC<SidebarContentProps> = ({ navigation, currentPath }) => {
  return (
    <div className="flex flex-col h-0 flex-1 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
      {/* Logo */}
      <div className="flex items-center h-16 flex-shrink-0 px-4 bg-primary-600">
        <div className="flex items-center space-x-3">
          <div className="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
            <svg
              className="w-5 h-5 text-primary-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
              />
            </svg>
          </div>
          <span className="text-xl font-bold text-white">DevCP</span>
        </div>
      </div>

      {/* Navigation */}
      <div className="flex-1 flex flex-col overflow-y-auto">
        <nav className="flex-1 px-2 py-4 space-y-1">
          {navigation.map((item) => {
            const isActive = currentPath === item.href
            return (
              <Link
                key={item.name}
                to={item.href}
                className={`${
                  isActive
                    ? 'sidebar-link-active'
                    : 'sidebar-link text-gray-700 dark:text-gray-300'
                } group flex items-center px-2 py-2 text-sm font-medium rounded-md`}
              >
                <item.icon
                  className={`${
                    isActive
                      ? 'text-primary-600 dark:text-primary-400'
                      : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
                  } mr-3 flex-shrink-0 h-5 w-5`}
                />
                {item.name}
              </Link>
            )
          })}
        </nav>
      </div>
    </div>
  )
}

export default DashboardLayout