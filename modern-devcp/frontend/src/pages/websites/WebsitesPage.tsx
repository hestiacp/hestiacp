import React, { useState } from 'react'
import { motion } from 'framer-motion'
import { PlusIcon, GlobeAltIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline'

const WebsitesPage: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('')

  const websites = [
    { id: '1', domain: 'example.com', status: 'active', ssl: true, traffic: '1.2K' },
    { id: '2', domain: 'test.com', status: 'active', ssl: false, traffic: '856' },
    { id: '3', domain: 'demo.org', status: 'suspended', ssl: true, traffic: '2.1K' },
  ]

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Sites Web</h1>
          <p className="text-gray-500 dark:text-gray-400">Gérez vos sites web et domaines</p>
        </div>
        <motion.button whileHover={{ scale: 1.05 }} className="btn-primary">
          <PlusIcon className="w-4 h-4 mr-2" />
          Nouveau site
        </motion.button>
      </div>

      <div className="card p-6">
        <div className="relative">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
          <input
            type="text"
            placeholder="Rechercher un site..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="input pl-10"
          />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {websites.map((site, index) => (
          <motion.div
            key={site.id}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: index * 0.1 }}
            className="card-hover p-6"
          >
            <div className="flex items-center justify-between mb-4">
              <GlobeAltIcon className="h-8 w-8 text-primary-600" />
              <span className={`badge ${site.status === 'active' ? 'badge-success' : 'badge-error'}`}>
                {site.status === 'active' ? 'Actif' : 'Suspendu'}
              </span>
            </div>
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              {site.domain}
            </h3>
            <div className="space-y-2 text-sm text-gray-500 dark:text-gray-400">
              <div className="flex justify-between">
                <span>SSL:</span>
                <span className={site.ssl ? 'text-success-600' : 'text-error-600'}>
                  {site.ssl ? 'Activé' : 'Désactivé'}
                </span>
              </div>
              <div className="flex justify-between">
                <span>Trafic mensuel:</span>
                <span>{site.traffic} visites</span>
              </div>
            </div>
          </motion.div>
        ))}
      </div>
    </div>
  )
}

export default WebsitesPage