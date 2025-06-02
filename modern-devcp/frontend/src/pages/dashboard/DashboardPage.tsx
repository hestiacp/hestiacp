import React from 'react'
import { motion } from 'framer-motion'
import {
  CpuChipIcon,
  CircleStackIcon,
  ServerIcon,
  UsersIcon,
  GlobeAltIcon,
  EnvelopeIcon,
  ShieldCheckIcon,
  ClockIcon,
} from '@heroicons/react/24/outline'
import SystemMetricsChart from '../../components/charts/SystemMetricsChart'
import ServiceStatusCard from '../../components/ui/ServiceStatusCard'
import StatsCard from '../../components/ui/StatsCard'

const DashboardPage: React.FC = () => {
  // Mock data - in real app, this would come from API
  const stats = [
    {
      name: 'Utilisateurs',
      value: '12',
      change: '+2',
      changeType: 'increase' as const,
      icon: UsersIcon,
    },
    {
      name: 'Sites Web',
      value: '24',
      change: '+3',
      changeType: 'increase' as const,
      icon: GlobeAltIcon,
    },
    {
      name: 'Bases de données',
      value: '18',
      change: '+1',
      changeType: 'increase' as const,
      icon: CircleStackIcon,
    },
    {
      name: 'Comptes mail',
      value: '45',
      change: '+5',
      changeType: 'increase' as const,
      icon: EnvelopeIcon,
    },
  ]

  const services = [
    { name: 'Apache', status: 'online', uptime: '99.9%' },
    { name: 'MySQL', status: 'online', uptime: '99.8%' },
    { name: 'Postfix', status: 'online', uptime: '99.7%' },
    { name: 'Dovecot', status: 'online', uptime: '99.9%' },
    { name: 'BIND', status: 'warning', uptime: '98.5%' },
    { name: 'Fail2ban', status: 'online', uptime: '100%' },
  ]

  const systemMetrics = {
    cpu: 45,
    memory: 68,
    disk: 32,
    network: 15,
  }

  return (
    <div className="space-y-6">
      {/* Welcome section */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5 }}
        className="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl p-6 text-white"
      >
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold">Bienvenue sur DevCP</h1>
            <p className="mt-1 text-primary-100">
              Votre panneau de contrôle d'hébergement moderne
            </p>
          </div>
          <div className="flex items-center space-x-2 text-primary-100">
            <ClockIcon className="h-5 w-5" />
            <span className="text-sm">
              Dernière connexion : {new Date().toLocaleDateString('fr-FR')}
            </span>
          </div>
        </div>
      </motion.div>

      {/* Stats cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {stats.map((stat, index) => (
          <motion.div
            key={stat.name}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: index * 0.1 }}
          >
            <StatsCard {...stat} />
          </motion.div>
        ))}
      </div>

      {/* System metrics and services */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* System metrics chart */}
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, delay: 0.4 }}
          className="card p-6"
        >
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
              Métriques Système
            </h2>
            <div className="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
              <div className="w-2 h-2 bg-success-500 rounded-full animate-pulse"></div>
              <span>Temps réel</span>
            </div>
          </div>
          <SystemMetricsChart metrics={systemMetrics} />
        </motion.div>

        {/* Services status */}
        <motion.div
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, delay: 0.5 }}
          className="card p-6"
        >
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
              État des Services
            </h2>
            <div className="flex items-center space-x-2">
              <ShieldCheckIcon className="h-5 w-5 text-success-500" />
              <span className="text-sm text-gray-500 dark:text-gray-400">
                {services.filter(s => s.status === 'online').length}/{services.length} en ligne
              </span>
            </div>
          </div>
          <div className="space-y-3">
            {services.map((service, index) => (
              <motion.div
                key={service.name}
                initial={{ opacity: 0, x: 20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.3, delay: 0.6 + index * 0.05 }}
              >
                <ServiceStatusCard {...service} />
              </motion.div>
            ))}
          </div>
        </motion.div>
      </div>

      {/* Recent activity */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay: 0.7 }}
        className="card p-6"
      >
        <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-6">
          Activité Récente
        </h2>
        <div className="space-y-4">
          {[
            {
              action: 'Nouveau site web créé',
              details: 'example.com',
              time: 'Il y a 2 heures',
              type: 'success',
            },
            {
              action: 'Base de données sauvegardée',
              details: 'wordpress_db',
              time: 'Il y a 4 heures',
              type: 'info',
            },
            {
              action: 'Certificat SSL renouvelé',
              details: 'secure.example.com',
              time: 'Il y a 6 heures',
              type: 'success',
            },
            {
              action: 'Tentative de connexion échouée',
              details: 'IP: 192.168.1.100',
              time: 'Il y a 8 heures',
              type: 'warning',
            },
          ].map((activity, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, x: -20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.3, delay: 0.8 + index * 0.05 }}
              className="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-700"
            >
              <div className="flex items-center space-x-3">
                <div
                  className={`w-2 h-2 rounded-full ${
                    activity.type === 'success'
                      ? 'bg-success-500'
                      : activity.type === 'warning'
                      ? 'bg-warning-500'
                      : 'bg-primary-500'
                  }`}
                />
                <div>
                  <p className="text-sm font-medium text-gray-900 dark:text-white">
                    {activity.action}
                  </p>
                  <p className="text-xs text-gray-500 dark:text-gray-400">
                    {activity.details}
                  </p>
                </div>
              </div>
              <span className="text-xs text-gray-500 dark:text-gray-400">
                {activity.time}
              </span>
            </motion.div>
          ))}
        </div>
      </motion.div>
    </div>
  )
}

export default DashboardPage