import React from 'react'
import { motion } from 'framer-motion'
import { Link } from 'react-router-dom'
import { HomeIcon } from '@heroicons/react/24/outline'

const NotFoundPage: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-md">
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
          className="text-center"
        >
          <div className="mx-auto w-24 h-24 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center mb-8">
            <span className="text-4xl font-bold text-primary-600 dark:text-primary-400">
              404
            </span>
          </div>
          
          <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Page non trouvée
          </h1>
          
          <p className="text-gray-600 dark:text-gray-400 mb-8">
            Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
          </p>
          
          <motion.div
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
          >
            <Link
              to="/dashboard"
              className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200"
            >
              <HomeIcon className="w-5 h-5 mr-2" />
              Retour au dashboard
            </Link>
          </motion.div>
        </motion.div>
      </div>
    </div>
  )
}

export default NotFoundPage