import React from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import { useAuthStore } from './stores/authStore'
import { useThemeStore } from './stores/themeStore'

// Layouts
import AuthLayout from './components/layouts/AuthLayout'
import DashboardLayout from './components/layouts/DashboardLayout'

// Pages
import LoginPage from './pages/auth/LoginPage'
import DashboardPage from './pages/dashboard/DashboardPage'
import UsersPage from './pages/users/UsersPage'
import WebsitesPage from './pages/websites/WebsitesPage'
import DatabasesPage from './pages/databases/DatabasesPage'
import DnsPage from './pages/dns/DnsPage'
import MailPage from './pages/mail/MailPage'
import FilesPage from './pages/files/FilesPage'
import BackupsPage from './pages/backups/BackupsPage'
import SettingsPage from './pages/settings/SettingsPage'
import NotFoundPage from './pages/NotFoundPage'

// Protected Route Component
function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  
  if (!isAuthenticated) {
    return <Navigate to="/login" replace />
  }
  
  return <>{children}</>
}

// Public Route Component (redirect if authenticated)
function PublicRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated } = useAuthStore()
  
  if (isAuthenticated) {
    return <Navigate to="/dashboard" replace />
  }
  
  return <>{children}</>
}

function App() {
  const { theme } = useThemeStore()
  const isDark = theme === 'dark'

  // Apply theme to document
  React.useEffect(() => {
    if (theme === 'dark') {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }, [theme])

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
      <Routes>
        {/* Public Routes */}
        <Route path="/login" element={
          <PublicRoute>
            <AuthLayout>
              <LoginPage />
            </AuthLayout>
          </PublicRoute>
        } />

        {/* Protected Routes */}
        <Route path="/dashboard" element={
          <ProtectedRoute>
            <DashboardLayout>
              <DashboardPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/users" element={
          <ProtectedRoute>
            <DashboardLayout>
              <UsersPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/websites" element={
          <ProtectedRoute>
            <DashboardLayout>
              <WebsitesPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/databases" element={
          <ProtectedRoute>
            <DashboardLayout>
              <DatabasesPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/dns" element={
          <ProtectedRoute>
            <DashboardLayout>
              <DnsPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/mail" element={
          <ProtectedRoute>
            <DashboardLayout>
              <MailPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/files" element={
          <ProtectedRoute>
            <DashboardLayout>
              <FilesPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/backups" element={
          <ProtectedRoute>
            <DashboardLayout>
              <BackupsPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        <Route path="/settings" element={
          <ProtectedRoute>
            <DashboardLayout>
              <SettingsPage />
            </DashboardLayout>
          </ProtectedRoute>
        } />

        {/* Redirects */}
        <Route path="/" element={<Navigate to="/dashboard" replace />} />
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
      
      {/* Toast Notifications */}
      <Toaster
        position="top-right"
        toastOptions={{
          duration: 4000,
          style: {
            background: isDark ? '#374151' : '#ffffff',
            color: isDark ? '#ffffff' : '#1f2937',
            border: `1px solid ${isDark ? '#4b5563' : '#e5e7eb'}`,
          },
          success: {
            iconTheme: {
              primary: '#10b981',
              secondary: '#ffffff',
            },
          },
          error: {
            iconTheme: {
              primary: '#ef4444',
              secondary: '#ffffff',
            },
          },
        }}
      />
    </div>
  )
}

export default App