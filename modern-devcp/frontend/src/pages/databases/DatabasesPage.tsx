import React from 'react'
import { motion } from 'framer-motion'
import { PlusIcon, CircleStackIcon } from '@heroicons/react/24/outline'

const DatabasesPage: React.FC = () => {
  const databases = [
    { id: '1', name: 'wordpress_db', size: '45.2 MB', tables: 12, type: 'MySQL' },
    { id: '2', name: 'ecommerce_db', size: '128.7 MB', tables: 24, type: 'MySQL' },
    { id: '3', name: 'analytics_db', size: '2.1 GB', tables: 8, type: 'PostgreSQL' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Bases de Données</h1>
          <p className="text-gray-500 dark:text-gray-400">Gérez vos bases de données</p>
        </div>
        <motion.button whileHover={{ scale: 1.05 }} className="btn-primary">
          <PlusIcon className="w-4 h-4 mr-2" />
          Nouvelle base
        </motion.button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {databases.map((db, index) => (
          <motion.div
            key={db.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.1 }}
            className="card-hover p-6"
          >
            <div className="flex items-center mb-4">
              <CircleStackIcon className="h-8 w-8 text-primary-600 mr-3" />
              <span className="badge badge-primary">{db.type}</span>
            </div>
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              {db.name}
            </h3>
            <div className="space-y-2 text-sm text-gray-500 dark:text-gray-400">
              <div className="flex justify-between">
                <span>Taille:</span>
                <span>{db.size}</span>
              </div>
              <div className="flex justify-between">
                <span>Tables:</span>
                <span>{db.tables}</span>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  )
}

export default DatabasesPage