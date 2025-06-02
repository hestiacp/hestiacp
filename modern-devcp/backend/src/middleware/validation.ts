import { Request, Response, NextFunction } from 'express'
import Joi from 'joi'
import { logger } from '@/utils/logger'

export const validateRequest = (schema: Joi.ObjectSchema) => {
  return (req: Request, res: Response, next: NextFunction): void => {
    const { error, value } = schema.validate(req.body, {
      abortEarly: false,
      stripUnknown: true,
    })

    if (error) {
      const errorDetails = error.details.map(detail => ({
        field: detail.path.join('.'),
        message: detail.message,
      }))

      logger.warn('Validation error', {
        url: req.url,
        method: req.method,
        errors: errorDetails,
        body: req.body,
      })

      res.status(400).json({
        error: 'Validation failed',
        details: errorDetails,
      })
      return
    }

    // Replace req.body with validated and sanitized data
    req.body = value
    next()
  }
}

export const validateQuery = (schema: Joi.ObjectSchema) => {
  return (req: Request, res: Response, next: NextFunction): void => {
    const { error, value } = schema.validate(req.query, {
      abortEarly: false,
      stripUnknown: true,
    })

    if (error) {
      const errorDetails = error.details.map(detail => ({
        field: detail.path.join('.'),
        message: detail.message,
      }))

      logger.warn('Query validation error', {
        url: req.url,
        method: req.method,
        errors: errorDetails,
        query: req.query,
      })

      res.status(400).json({
        error: 'Query validation failed',
        details: errorDetails,
      })
      return
    }

    // Replace req.query with validated and sanitized data
    req.query = value
    next()
  }
}

export const validateParams = (schema: Joi.ObjectSchema) => {
  return (req: Request, res: Response, next: NextFunction): void => {
    const { error, value } = schema.validate(req.params, {
      abortEarly: false,
      stripUnknown: true,
    })

    if (error) {
      const errorDetails = error.details.map(detail => ({
        field: detail.path.join('.'),
        message: detail.message,
      }))

      logger.warn('Params validation error', {
        url: req.url,
        method: req.method,
        errors: errorDetails,
        params: req.params,
      })

      res.status(400).json({
        error: 'Parameters validation failed',
        details: errorDetails,
      })
      return
    }

    // Replace req.params with validated and sanitized data
    req.params = value
    next()
  }
}