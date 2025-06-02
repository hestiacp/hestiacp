import { Request, Response, NextFunction } from 'express'
import jwt from 'jsonwebtoken'
import { config } from '@/config/config'
import { logger } from '@/utils/logger'

export interface AuthenticatedRequest extends Request {
  user?: {
    id: string
    username: string
    email: string
    role: string
  }
}

export const authMiddleware = (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): void => {
  try {
    const authHeader = req.headers.authorization
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      res.status(401).json({
        error: 'Access denied. No token provided.',
      })
      return
    }

    const token = authHeader.substring(7) // Remove 'Bearer ' prefix

    try {
      const decoded = jwt.verify(token, config.jwt.secret) as any
      req.user = decoded
      next()
    } catch (jwtError) {
      logger.warn('Invalid JWT token', { token: token.substring(0, 20) + '...' })
      res.status(401).json({
        error: 'Invalid token.',
      })
    }
  } catch (error) {
    logger.error('Auth middleware error', error)
    res.status(500).json({
      error: 'Internal server error.',
    })
  }
}

export const adminMiddleware = (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): void => {
  if (!req.user) {
    res.status(401).json({
      error: 'Access denied. Authentication required.',
    })
    return
  }

  if (req.user.role !== 'ADMIN') {
    res.status(403).json({
      error: 'Access denied. Admin privileges required.',
    })
    return
  }

  next()
}

export const optionalAuthMiddleware = (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): void => {
  try {
    const authHeader = req.headers.authorization
    
    if (authHeader && authHeader.startsWith('Bearer ')) {
      const token = authHeader.substring(7)
      
      try {
        const decoded = jwt.verify(token, config.jwt.secret) as any
        req.user = decoded
      } catch (jwtError) {
        // Token is invalid, but we continue without user
        logger.debug('Invalid token in optional auth', { error: jwtError })
      }
    }
    
    next()
  } catch (error) {
    logger.error('Optional auth middleware error', error)
    next() // Continue without authentication
  }
}