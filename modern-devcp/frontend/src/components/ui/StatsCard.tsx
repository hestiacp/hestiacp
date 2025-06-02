import React from 'react'
import { motion } from 'framer-motion'

interface StatsCardProps {
  name: string
  value: string
  change: string
  changeType: 'increase' | 'decrease'
  icon: React.ComponentType<{ className?: string }>
}

const StatsCard: React.FC<StatsCardProps> = ({
  name,
  value,
  change,
  changeType,
  icon: Icon,
}) => {
  return (
    <motion.div
      whileHover={{ scale: 1.02 }}
      className="card-hover p-6"
    >
      <div className="flex items-center">
        <div className="flex-shrink-0">
          <div className="w-12 h-12 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
            <Icon className="w-6 h-6 text-primary-600 dark:text-primary-400" />
          </div>
        </div>
        <div className="ml-4 flex-1">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-500 dark:text-gray-400">
                {name}
              </p>
              <p className="text-2xl font-bold text-gray-900 dark:text-white">
                {value}
              </p>
            </div>
            <div
              className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                changeType === 'increase'
                  ? 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200'
                  : 'bg-error-100 text-error-800 dark:bg-error-900 dark:text-error-200'
              }`}
            >
              <svg
                className={`w-3 h-3 mr-1 ${
                  changeType === 'increase' ? 'rotate-0' : 'rotate-180'
                }`}
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fillRule="evenodd"
                  d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z"
                  clipRule="evenodd"
                />
              </svg>
              {change}
            </div>
          </div>
        </div>
      </div>
    </motion.div>
  )
}

export default StatsCard