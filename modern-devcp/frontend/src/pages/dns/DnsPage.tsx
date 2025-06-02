import React from 'react'
import { motion } from 'framer-motion'

const DnsPage: React.FC = () => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Gestion DNS</h1>
        <p className="text-gray-500 dark:text-gray-400">Gérez vos enregistrements DNS</p>
      </div>
      
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="card p-8 text-center"
      >
        <div className="w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
          </svg>
        </div>
        <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
          En cours de développement
        </h3>
        <p className="text-gray-500 dark:text-gray-400">
          Cette fonctionnalité sera bientôt disponible.
        </p>
      </motion.div>
    </div>
  )
}

export default DnsPage
