import winston from 'winston'
import { config } from '@/config/config'

// Custom log format
const logFormat = winston.format.combine(
  winston.format.timestamp({
    format: 'YYYY-MM-DD HH:mm:ss',
  }),
  winston.format.errors({ stack: true }),
  winston.format.json(),
  winston.format.printf(({ timestamp, level, message, stack, ...meta }) => {
    let log = `${timestamp} [${level.toUpperCase()}]: ${message}`
    
    if (stack) {
      log += `\n${stack}`
    }
    
    if (Object.keys(meta).length > 0) {
      log += `\n${JSON.stringify(meta, null, 2)}`
    }
    
    return log
  })
)

// Create logger instance
export const logger = winston.createLogger({
  level: config.logging.level,
  format: logFormat,
  defaultMeta: { service: 'devcp-api' },
  transports: [
    // Console transport
    new winston.transports.Console({
      format: winston.format.combine(
        winston.format.colorize(),
        winston.format.simple()
      ),
    }),
    
    // File transport for errors
    new winston.transports.File({
      filename: 'logs/error.log',
      level: 'error',
      maxsize: 5242880, // 5MB
      maxFiles: 5,
    }),
    
    // File transport for all logs
    new winston.transports.File({
      filename: config.logging.file,
      maxsize: config.logging.maxSize,
      maxFiles: parseInt(config.logging.maxFiles, 10),
    }),
  ],
})

// Create logs directory if it doesn't exist
import { existsSync, mkdirSync } from 'fs'
import { dirname } from 'path'

const logDir = dirname(config.logging.file)
if (!existsSync(logDir)) {
  mkdirSync(logDir, { recursive: true })
}

// Handle uncaught exceptions and unhandled rejections
logger.exceptions.handle(
  new winston.transports.File({ filename: 'logs/exceptions.log' })
)

logger.rejections.handle(
  new winston.transports.File({ filename: 'logs/rejections.log' })
)

// Export logger methods for convenience
export const logInfo = (message: string, meta?: any) => logger.info(message, meta)
export const logError = (message: string, meta?: any) => logger.error(message, meta)
export const logWarn = (message: string, meta?: any) => logger.warn(message, meta)
export const logDebug = (message: string, meta?: any) => logger.debug(message, meta)