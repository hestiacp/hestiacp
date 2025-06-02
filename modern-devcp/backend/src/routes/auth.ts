import { Router } from 'express'
import { AuthController } from '@/controllers/AuthController'
import { validateRequest } from '@/middleware/validation'
import { loginSchema, registerSchema, refreshTokenSchema } from '@/utils/validation'

const router = Router()
const authController = new AuthController()

// POST /api/auth/login
router.post('/login', validateRequest(loginSchema), authController.login)

// POST /api/auth/register
router.post('/register', validateRequest(registerSchema), authController.register)

// POST /api/auth/refresh
router.post('/refresh', validateRequest(refreshTokenSchema), authController.refreshToken)

// POST /api/auth/logout
router.post('/logout', authController.logout)

// GET /api/auth/me
router.get('/me', authController.getCurrentUser)

export default router