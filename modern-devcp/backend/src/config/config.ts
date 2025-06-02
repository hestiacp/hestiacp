import dotenv from 'dotenv'

dotenv.config()

export const config = {
  env: process.env.NODE_ENV || 'development',
  port: parseInt(process.env.PORT || '3001', 10),
  host: process.env.HOST || '0.0.0.0',

  database: {
    url: process.env.DATABASE_URL || 'postgresql://devcp:password@localhost:5432/devcp?schema=public',
  },

  jwt: {
    secret: process.env.JWT_SECRET || 'your-super-secret-jwt-key',
    expiresIn: process.env.JWT_EXPIRES_IN || '7d',
    refreshSecret: process.env.JWT_REFRESH_SECRET || 'your-super-secret-refresh-key',
    refreshExpiresIn: process.env.JWT_REFRESH_EXPIRES_IN || '30d',
  },

  cors: {
    origin: process.env.CORS_ORIGIN || 'http://localhost:5173',
  },

  rateLimit: {
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS || '900000', 10), // 15 minutes
    maxRequests: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS || '100', 10),
  },

  email: {
    smtp: {
      host: process.env.SMTP_HOST || 'localhost',
      port: parseInt(process.env.SMTP_PORT || '587', 10),
      secure: process.env.SMTP_SECURE === 'true',
      auth: {
        user: process.env.SMTP_USER || '',
        pass: process.env.SMTP_PASS || '',
      },
    },
    from: process.env.SMTP_FROM || 'noreply@devcp.local',
  },

  upload: {
    maxFileSize: parseInt(process.env.MAX_FILE_SIZE || '10485760', 10), // 10MB
    path: process.env.UPLOAD_PATH || './uploads',
  },

  monitoring: {
    interval: parseInt(process.env.MONITOR_INTERVAL || '30000', 10), // 30 seconds
    retentionDays: parseInt(process.env.METRICS_RETENTION_DAYS || '30', 10),
  },

  security: {
    bcryptRounds: parseInt(process.env.BCRYPT_ROUNDS || '12', 10),
    sessionSecret: process.env.SESSION_SECRET || 'your-session-secret',
  },

  backup: {
    path: process.env.BACKUP_PATH || './backups',
    retentionDays: parseInt(process.env.BACKUP_RETENTION_DAYS || '30', 10),
  },

  logging: {
    level: process.env.LOG_LEVEL || 'info',
    file: process.env.LOG_FILE || './logs/devcp.log',
    maxSize: process.env.LOG_MAX_SIZE || '10m',
    maxFiles: process.env.LOG_MAX_FILES || '5',
  },
} as const

// Validate required environment variables
const requiredEnvVars = ['JWT_SECRET', 'DATABASE_URL']

for (const envVar of requiredEnvVars) {
  if (!process.env[envVar]) {
    console.error(`❌ Missing required environment variable: ${envVar}`)
    process.exit(1)
  }
}