import { Request, Response, NextFunction } from 'express'
import { logger } from '@/utils/logger'
import { config } from '@/config/config'

export interface AppError extends Error {
  statusCode?: number
  isOperational?: boolean
}

export const errorHandler = (
  error: AppError,
  req: Request,
  res: Response,
  next: NextFunction
): void => {
  let { statusCode = 500, message } = error

  // Log error
  logger.error('Error occurred', {
    error: message,
    stack: error.stack,
    url: req.url,
    method: req.method,
    ip: req.ip,
    userAgent: req.get('User-Agent'),
  })

  // Prisma errors
  if (error.name === 'PrismaClientKnownRequestError') {
    statusCode = 400
    message = 'Database operation failed'
  }

  // JWT errors
  if (error.name === 'JsonWebTokenError') {
    statusCode = 401
    message = 'Invalid token'
  }

  if (error.name === 'TokenExpiredError') {
    statusCode = 401
    message = 'Token expired'
  }

  // Validation errors
  if (error.name === 'ValidationError') {
    statusCode = 400
    message = 'Validation failed'
  }

  // Multer errors (file upload)
  if (error.name === 'MulterError') {
    statusCode = 400
    if (error.message.includes('File too large')) {
      message = 'File size exceeds limit'
    } else {
      message = 'File upload error'
    }
  }

  // Don't leak error details in production
  if (config.env === 'production' && statusCode === 500) {
    message = 'Internal server error'
  }

  res.status(statusCode).json({
    error: message,
    ...(config.env === 'development' && {
      stack: error.stack,
      details: error,
    }),
  })
}

export const notFoundHandler = (
  req: Request,
  res: Response,
  next: NextFunction
): void => {
  const error: AppError = new Error(`Route ${req.originalUrl} not found`)
  error.statusCode = 404
  error.isOperational = true
  
  next(error)
}

export const asyncHandler = (
  fn: (req: Request, res: Response, next: NextFunction) => Promise<any>
) => {
  return (req: Request, res: Response, next: NextFunction) => {
    Promise.resolve(fn(req, res, next)).catch(next)
  }
}

export const createError = (message: string, statusCode: number = 500): AppError => {
  const error: AppError = new Error(message)
  error.statusCode = statusCode
  error.isOperational = true
  return error
}