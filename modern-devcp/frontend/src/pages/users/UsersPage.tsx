import React, { useState } from 'react'
import { motion } from 'framer-motion'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  UserCircleIcon,
} from '@heroicons/react/24/outline'

interface User {
  id: string
  username: string
  email: string
  role: 'admin' | 'user'
  status: 'active' | 'suspended'
  createdAt: string
  lastLogin?: string
}

const UsersPage: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedRole, setSelectedRole] = useState<string>('all')

  // Mock data
  const users: User[] = [
    {
      id: '1',
      username: 'admin',
      email: 'admin@devcp.com',
      role: 'admin',
      status: 'active',
      createdAt: '2024-01-15',
      lastLogin: '2024-06-02',
    },
    {
      id: '2',
      username: 'john_doe',
      email: 'john@example.com',
      role: 'user',
      status: 'active',
      createdAt: '2024-02-20',
      lastLogin: '2024-06-01',
    },
    {
      id: '3',
      username: 'jane_smith',
      email: 'jane@example.com',
      role: 'user',
      status: 'active',
      createdAt: '2024-03-10',
      lastLogin: '2024-05-30',
    },
    {
      id: '4',
      username: 'suspended_user',
      email: 'suspended@example.com',
      role: 'user',
      status: 'suspended',
      createdAt: '2024-01-05',
      lastLogin: '2024-05-15',
    },
  ]

  const filteredUsers = users.filter(user => {
    const matchesSearch = user.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         user.email.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesRole = selectedRole === 'all' || user.role === selectedRole
    return matchesSearch && matchesRole
  })

  const getRoleBadge = (role: string) => {
    return role === 'admin' ? 'badge-primary' : 'badge-gray'
  }

  const getStatusBadge = (status: string) => {
    return status === 'active' ? 'badge-success' : 'badge-error'
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
            Gestion des Utilisateurs
          </h1>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Gérez les comptes utilisateurs et leurs permissions
          </p>
        </div>
        <motion.button
          whileHover={{ scale: 1.05 }}
          whileTap={{ scale: 0.95 }}
          className="btn-primary mt-4 sm:mt-0"
        >
          <PlusIcon className="w-4 h-4 mr-2" />
          Nouvel utilisateur
        </motion.button>
      </div>

      {/* Filters */}
      <div className="card p-6">
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="flex-1">
            <div className="relative">
              <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                type="text"
                placeholder="Rechercher un utilisateur..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="input pl-10"
              />
            </div>
          </div>
          <div>
            <select
              value={selectedRole}
              onChange={(e) => setSelectedRole(e.target.value)}
              className="input"
            >
              <option value="all">Tous les rôles</option>
              <option value="admin">Administrateur</option>
              <option value="user">Utilisateur</option>
            </select>
          </div>
        </div>
      </div>

      {/* Users table */}
      <div className="card overflow-hidden">
        <div className="overflow-x-auto">
          <table className="table">
            <thead className="table-header">
              <tr>
                <th className="table-header-cell">Utilisateur</th>
                <th className="table-header-cell">Email</th>
                <th className="table-header-cell">Rôle</th>
                <th className="table-header-cell">Statut</th>
                <th className="table-header-cell">Dernière connexion</th>
                <th className="table-header-cell">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
              {filteredUsers.map((user, index) => (
                <motion.tr
                  key={user.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.3, delay: index * 0.05 }}
                  className="table-row"
                >
                  <td className="table-cell">
                    <div className="flex items-center">
                      <UserCircleIcon className="h-8 w-8 text-gray-400 mr-3" />
                      <div>
                        <div className="text-sm font-medium text-gray-900 dark:text-white">
                          {user.username}
                        </div>
                        <div className="text-sm text-gray-500 dark:text-gray-400">
                          ID: {user.id}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td className="table-cell">
                    <div className="text-sm text-gray-900 dark:text-white">
                      {user.email}
                    </div>
                  </td>
                  <td className="table-cell">
                    <span className={`badge ${getRoleBadge(user.role)}`}>
                      {user.role === 'admin' ? 'Administrateur' : 'Utilisateur'}
                    </span>
                  </td>
                  <td className="table-cell">
                    <span className={`badge ${getStatusBadge(user.status)}`}>
                      {user.status === 'active' ? 'Actif' : 'Suspendu'}
                    </span>
                  </td>
                  <td className="table-cell">
                    <div className="text-sm text-gray-900 dark:text-white">
                      {user.lastLogin ? new Date(user.lastLogin).toLocaleDateString('fr-FR') : 'Jamais'}
                    </div>
                  </td>
                  <td className="table-cell">
                    <div className="flex items-center space-x-2">
                      <motion.button
                        whileHover={{ scale: 1.1 }}
                        whileTap={{ scale: 0.9 }}
                        className="p-1 text-gray-400 hover:text-primary-600 dark:hover:text-primary-400"
                        title="Modifier"
                      >
                        <PencilIcon className="h-4 w-4" />
                      </motion.button>
                      <motion.button
                        whileHover={{ scale: 1.1 }}
                        whileTap={{ scale: 0.9 }}
                        className="p-1 text-gray-400 hover:text-error-600 dark:hover:text-error-400"
                        title="Supprimer"
                      >
                        <TrashIcon className="h-4 w-4" />
                      </motion.button>
                    </div>
                  </td>
                </motion.tr>
              ))}
            </tbody>
          </table>
        </div>

        {filteredUsers.length === 0 && (
          <div className="text-center py-12">
            <UserCircleIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
              Aucun utilisateur trouvé
            </h3>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Aucun utilisateur ne correspond à vos critères de recherche.
            </p>
          </div>
        )}
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="card p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                <UserCircleIcon className="w-5 h-5 text-primary-600 dark:text-primary-400" />
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                Total utilisateurs
              </p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">
                {users.length}
              </p>
            </div>
          </div>
        </div>

        <div className="card p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-success-100 dark:bg-success-900 rounded-lg flex items-center justify-center">
                <UserCircleIcon className="w-5 h-5 text-success-600 dark:text-success-400" />
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                Utilisateurs actifs
              </p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">
                {users.filter(u => u.status === 'active').length}
              </p>
            </div>
          </div>
        </div>

        <div className="card p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-warning-100 dark:bg-warning-900 rounded-lg flex items-center justify-center">
                <UserCircleIcon className="w-5 h-5 text-warning-600 dark:text-warning-400" />
              </div>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                Administrateurs
              </p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">
                {users.filter(u => u.role === 'admin').length}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default UsersPage