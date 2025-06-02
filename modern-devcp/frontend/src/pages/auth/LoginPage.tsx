import React, { useState } from 'react'
import { motion } from 'framer-motion'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { EyeIcon, EyeSlashIcon } from '@heroicons/react/24/outline'
import { useAuthStore } from '../../stores/authStore'
import toast from 'react-hot-toast'

const loginSchema = z.object({
  username: z.string().min(1, 'Le nom d\'utilisateur est requis'),
  password: z.string().min(1, 'Le mot de passe est requis'),
  rememberMe: z.boolean().optional(),
})

type LoginFormData = z.infer<typeof loginSchema>

const LoginPage: React.FC = () => {
  const [showPassword, setShowPassword] = useState(false)
  const { login, setLoading, isLoading } = useAuthStore()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  })

  const onSubmit = async (data: LoginFormData) => {
    setLoading(true)
    
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000))
      
      // Mock successful login
      if (data.username === 'admin' && data.password === 'admin') {
        const mockUser = {
          id: '1',
          username: data.username,
          email: 'admin@devcp.com',
          role: 'admin' as const,
          avatar: 'https://ui-avatars.com/api/?name=Admin&background=3b82f6&color=fff',
          createdAt: new Date().toISOString(),
          lastLogin: new Date().toISOString(),
        }
        
        const mockToken = 'mock-jwt-token'
        
        login(mockUser, mockToken)
        toast.success('Connexion réussie !')
      } else {
        throw new Error('Identifiants invalides')
      }
    } catch (error) {
      toast.error(error instanceof Error ? error.message : 'Erreur de connexion')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-gray-900 dark:text-white text-center">
          Connexion
        </h2>
        <p className="mt-2 text-sm text-gray-600 dark:text-gray-400 text-center">
          Connectez-vous à votre panneau de contrôle
        </p>
      </div>

      <form className="space-y-6" onSubmit={handleSubmit(onSubmit)}>
        <div>
          <label htmlFor="username" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Nom d'utilisateur
          </label>
          <div className="mt-1">
            <input
              {...register('username')}
              type="text"
              autoComplete="username"
              className={`input ${errors.username ? 'input-error' : ''}`}
              placeholder="Entrez votre nom d'utilisateur"
            />
            {errors.username && (
              <p className="mt-1 text-sm text-error-600">{errors.username.message}</p>
            )}
          </div>
        </div>

        <div>
          <label htmlFor="password" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
            Mot de passe
          </label>
          <div className="mt-1 relative">
            <input
              {...register('password')}
              type={showPassword ? 'text' : 'password'}
              autoComplete="current-password"
              className={`input pr-10 ${errors.password ? 'input-error' : ''}`}
              placeholder="Entrez votre mot de passe"
            />
            <button
              type="button"
              className="absolute inset-y-0 right-0 pr-3 flex items-center"
              onClick={() => setShowPassword(!showPassword)}
            >
              {showPassword ? (
                <EyeSlashIcon className="h-5 w-5 text-gray-400" />
              ) : (
                <EyeIcon className="h-5 w-5 text-gray-400" />
              )}
            </button>
            {errors.password && (
              <p className="mt-1 text-sm text-error-600">{errors.password.message}</p>
            )}
          </div>
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <input
              {...register('rememberMe')}
              id="remember-me"
              type="checkbox"
              className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
            />
            <label htmlFor="remember-me" className="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Se souvenir de moi
            </label>
          </div>

          <div className="text-sm">
            <a
              href="#"
              className="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
              Mot de passe oublié ?
            </a>
          </div>
        </div>

        <div>
          <motion.button
            type="submit"
            disabled={isLoading}
            className="btn-primary w-full"
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.98 }}
          >
            {isLoading ? (
              <div className="flex items-center justify-center">
                <div className="loading-dots">
                  <div style={{ '--i': 0 } as any}></div>
                  <div style={{ '--i': 1 } as any}></div>
                  <div style={{ '--i': 2 } as any}></div>
                </div>
                <span className="ml-2">Connexion...</span>
              </div>
            ) : (
              'Se connecter'
            )}
          </motion.button>
        </div>
      </form>

      {/* Demo credentials */}
      <div className="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
        <h3 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Identifiants de démonstration :
        </h3>
        <div className="text-sm text-gray-600 dark:text-gray-400 space-y-1">
          <p><strong>Utilisateur :</strong> admin</p>
          <p><strong>Mot de passe :</strong> admin</p>
        </div>
      </div>
    </div>
  )
}

export default LoginPage