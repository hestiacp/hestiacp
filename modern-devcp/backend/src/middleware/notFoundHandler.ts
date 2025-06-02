import { Request, Response, NextFunction } from 'express'

export const notFoundHandler = (
  req: Request,
  res: Response,
  next: NextFunction
): void => {
  res.status(404).json({
    error: 'Route not found',
    message: `The requested route ${req.method} ${req.originalUrl} was not found on this server.`,
    timestamp: new Date().toISOString(),
  })
}