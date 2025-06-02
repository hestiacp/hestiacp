import express from 'express'
import cors from 'cors'
import helmet from 'helmet'
import compression from 'compression'
import morgan from 'morgan'
import rateLimit from 'express-rate-limit'
import { createServer } from 'http'
import { Server as SocketIOServer } from 'socket.io'
import dotenv from 'dotenv'

import { config } from '@/config/config'
import { logger } from '@/utils/logger'
import { errorHandler } from '@/middleware/errorHandler'
import { notFoundHandler } from '@/middleware/notFoundHandler'
import { authMiddleware } from '@/middleware/auth'

// Routes
import authRoutes from '@/routes/auth'
import userRoutes from '@/routes/users'
import websiteRoutes from '@/routes/websites'
import databaseRoutes from '@/routes/databases'
import dnsRoutes from '@/routes/dns'
import mailRoutes from '@/routes/mail'
import fileRoutes from '@/routes/files'
import backupRoutes from '@/routes/backups'
import systemRoutes from '@/routes/system'
import settingsRoutes from '@/routes/settings'

// Services
import { SystemMonitorService } from '@/services/SystemMonitorService'
import { WebSocketService } from '@/services/WebSocketService'

// Load environment variables
dotenv.config()

class DevCPServer {
  private app: express.Application
  private server: any
  private io: SocketIOServer
  private systemMonitor: SystemMonitorService
  private wsService: WebSocketService

  constructor() {
    this.app = express()
    this.server = createServer(this.app)
    this.io = new SocketIOServer(this.server, {
      cors: {
        origin: config.cors.origin,
        methods: ['GET', 'POST'],
        credentials: true,
      },
    })
    
    this.systemMonitor = new SystemMonitorService()
    this.wsService = new WebSocketService(this.io)
    
    this.setupMiddleware()
    this.setupRoutes()
    this.setupErrorHandling()
    this.setupWebSocket()
  }

  private setupMiddleware(): void {
    // Security middleware
    this.app.use(helmet({
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'self'"],
          styleSrc: ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
          fontSrc: ["'self'", "https://fonts.gstatic.com"],
          imgSrc: ["'self'", "data:", "https:"],
          scriptSrc: ["'self'"],
          connectSrc: ["'self'", "ws:", "wss:"],
        },
      },
    }))

    // CORS
    this.app.use(cors({
      origin: config.cors.origin,
      credentials: true,
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
    }))

    // Compression
    this.app.use(compression())

    // Rate limiting
    const limiter = rateLimit({
      windowMs: config.rateLimit.windowMs,
      max: config.rateLimit.maxRequests,
      message: {
        error: 'Too many requests from this IP, please try again later.',
      },
      standardHeaders: true,
      legacyHeaders: false,
    })
    this.app.use('/api/', limiter)

    // Logging
    this.app.use(morgan('combined', {
      stream: {
        write: (message: string) => logger.info(message.trim()),
      },
    }))

    // Body parsing
    this.app.use(express.json({ limit: '10mb' }))
    this.app.use(express.urlencoded({ extended: true, limit: '10mb' }))

    // Health check
    this.app.get('/health', (req, res) => {
      res.status(200).json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        environment: config.env,
      })
    })
  }

  private setupRoutes(): void {
    // API routes
    this.app.use('/api/auth', authRoutes)
    this.app.use('/api/users', authMiddleware, userRoutes)
    this.app.use('/api/websites', authMiddleware, websiteRoutes)
    this.app.use('/api/databases', authMiddleware, databaseRoutes)
    this.app.use('/api/dns', authMiddleware, dnsRoutes)
    this.app.use('/api/mail', authMiddleware, mailRoutes)
    this.app.use('/api/files', authMiddleware, fileRoutes)
    this.app.use('/api/backups', authMiddleware, backupRoutes)
    this.app.use('/api/system', authMiddleware, systemRoutes)
    this.app.use('/api/settings', authMiddleware, settingsRoutes)

    // API documentation
    this.app.get('/api', (req, res) => {
      res.json({
        name: 'DevCP API',
        version: '1.0.0',
        description: 'Modern hosting control panel API',
        endpoints: {
          auth: '/api/auth',
          users: '/api/users',
          websites: '/api/websites',
          databases: '/api/databases',
          dns: '/api/dns',
          mail: '/api/mail',
          files: '/api/files',
          backups: '/api/backups',
          system: '/api/system',
          settings: '/api/settings',
        },
      })
    })
  }

  private setupErrorHandling(): void {
    // 404 handler
    this.app.use(notFoundHandler)

    // Global error handler
    this.app.use(errorHandler)
  }

  private setupWebSocket(): void {
    this.wsService.initialize()
    
    // Start system monitoring
    this.systemMonitor.start((metrics) => {
      this.wsService.broadcastSystemMetrics(metrics)
    })
  }

  public start(): void {
    this.server.listen(config.port, config.host, () => {
      logger.info(`🚀 DevCP Server started on ${config.host}:${config.port}`)
      logger.info(`📊 Environment: ${config.env}`)
      logger.info(`🔗 API: http://${config.host}:${config.port}/api`)
      logger.info(`💾 Database: ${config.database.url ? 'Connected' : 'Not configured'}`)
    })

    // Graceful shutdown
    process.on('SIGTERM', this.shutdown.bind(this))
    process.on('SIGINT', this.shutdown.bind(this))
  }

  private shutdown(): void {
    logger.info('🛑 Shutting down DevCP Server...')
    
    this.systemMonitor.stop()
    
    this.server.close(() => {
      logger.info('✅ Server closed')
      process.exit(0)
    })
  }
}

// Start the server
const server = new DevCPServer()
server.start()

export default DevCPServer