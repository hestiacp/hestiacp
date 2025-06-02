import React from 'react'
import { motion } from 'framer-motion'

interface ServiceStatusCardProps {
  name: string
  status: 'online' | 'offline' | 'warning'
  uptime: string
}

const ServiceStatusCard: React.FC<ServiceStatusCardProps> = ({
  name,
  status,
  uptime,
}) => {
  const getStatusColor = () => {
    switch (status) {
      case 'online':
        return 'bg-success-500'
      case 'offline':
        return 'bg-error-500'
      case 'warning':
        return 'bg-warning-500'
      default:
        return 'bg-gray-500'
    }
  }

  const getStatusText = () => {
    switch (status) {
      case 'online':
        return 'En ligne'
      case 'offline':
        return 'Hors ligne'
      case 'warning':
        return 'Attention'
      default:
        return 'Inconnu'
    }
  }

  return (
    <motion.div
      whileHover={{ scale: 1.01 }}
      className="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
    >
      <div className="flex items-center space-x-3">
        <div className="relative">
          <div className={`w-3 h-3 rounded-full ${getStatusColor()}`} />
          {status === 'online' && (
            <div className={`absolute inset-0 w-3 h-3 rounded-full ${getStatusColor()} animate-ping opacity-75`} />
          )}
        </div>
        <div>
          <p className="text-sm font-medium text-gray-900 dark:text-white">
            {name}
          </p>
          <p className="text-xs text-gray-500 dark:text-gray-400">
            {getStatusText()}
          </p>
        </div>
      </div>
      <div className="text-right">
        <p className="text-sm font-medium text-gray-900 dark:text-white">
          {uptime}
        </p>
        <p className="text-xs text-gray-500 dark:text-gray-400">
          Uptime
        </p>
      </div>
    </motion.div>
  )
}

export default ServiceStatusCard