import { Server as SocketIOServer, Socket } from 'socket.io'
import jwt from 'jsonwebtoken'
import { config } from '@/config/config'
import { logger } from '@/utils/logger'
import { SystemMetrics } from './SystemMonitorService'

interface AuthenticatedSocket extends Socket {
  userId?: string
  userRole?: string
}

export class WebSocketService {
  private io: SocketIOServer
  private connectedUsers: Map<string, AuthenticatedSocket> = new Map()

  constructor(io: SocketIOServer) {
    this.io = io
  }

  public initialize(): void {
    this.io.use(this.authenticateSocket.bind(this))
    this.io.on('connection', this.handleConnection.bind(this))
    
    logger.info('WebSocket service initialized')
  }

  private authenticateSocket(socket: AuthenticatedSocket, next: (err?: Error) => void): void {
    try {
      const token = socket.handshake.auth.token || socket.handshake.headers.authorization?.replace('Bearer ', '')
      
      if (!token) {
        return next(new Error('Authentication token required'))
      }

      const decoded = jwt.verify(token, config.jwt.secret) as any
      socket.userId = decoded.id
      socket.userRole = decoded.role
      
      next()
    } catch (error) {
      logger.warn('WebSocket authentication failed', { error: error.message })
      next(new Error('Invalid authentication token'))
    }
  }

  private handleConnection(socket: AuthenticatedSocket): void {
    if (!socket.userId) {
      socket.disconnect()
      return
    }

    logger.info(`User connected via WebSocket: ${socket.userId}`)
    this.connectedUsers.set(socket.userId, socket)

    // Join user-specific room
    socket.join(`user:${socket.userId}`)

    // Join admin room if user is admin
    if (socket.userRole === 'ADMIN') {
      socket.join('admin')
    }

    // Handle disconnection
    socket.on('disconnect', () => {
      logger.info(`User disconnected from WebSocket: ${socket.userId}`)
      this.connectedUsers.delete(socket.userId!)
    })

    // Handle ping/pong for connection health
    socket.on('ping', () => {
      socket.emit('pong', { timestamp: Date.now() })
    })

    // Handle system metrics subscription
    socket.on('subscribe:system-metrics', () => {
      if (socket.userRole === 'ADMIN') {
        socket.join('system-metrics')
        logger.debug(`Admin user ${socket.userId} subscribed to system metrics`)
      }
    })

    socket.on('unsubscribe:system-metrics', () => {
      socket.leave('system-metrics')
      logger.debug(`User ${socket.userId} unsubscribed from system metrics`)
    })

    // Handle website status subscription
    socket.on('subscribe:website-status', (websiteId: string) => {
      socket.join(`website:${websiteId}`)
      logger.debug(`User ${socket.userId} subscribed to website ${websiteId} status`)
    })

    socket.on('unsubscribe:website-status', (websiteId: string) => {
      socket.leave(`website:${websiteId}`)
      logger.debug(`User ${socket.userId} unsubscribed from website ${websiteId} status`)
    })

    // Send welcome message
    socket.emit('connected', {
      message: 'Connected to DevCP WebSocket',
      userId: socket.userId,
      timestamp: new Date().toISOString(),
    })
  }

  public broadcastSystemMetrics(metrics: SystemMetrics): void {
    this.io.to('system-metrics').emit('system-metrics', {
      type: 'system-metrics',
      data: metrics,
      timestamp: new Date().toISOString(),
    })
  }

  public notifyUser(userId: string, event: string, data: any): void {
    this.io.to(`user:${userId}`).emit(event, {
      type: event,
      data,
      timestamp: new Date().toISOString(),
    })
  }

  public notifyAdmins(event: string, data: any): void {
    this.io.to('admin').emit(event, {
      type: event,
      data,
      timestamp: new Date().toISOString(),
    })
  }

  public notifyWebsiteUsers(websiteId: string, event: string, data: any): void {
    this.io.to(`website:${websiteId}`).emit(event, {
      type: event,
      data,
      timestamp: new Date().toISOString(),
    })
  }

  public broadcastToAll(event: string, data: any): void {
    this.io.emit(event, {
      type: event,
      data,
      timestamp: new Date().toISOString(),
    })
  }

  public getConnectedUsersCount(): number {
    return this.connectedUsers.size
  }

  public isUserConnected(userId: string): boolean {
    return this.connectedUsers.has(userId)
  }

  public disconnectUser(userId: string): void {
    const socket = this.connectedUsers.get(userId)
    if (socket) {
      socket.disconnect()
      this.connectedUsers.delete(userId)
      logger.info(`Forcibly disconnected user: ${userId}`)
    }
  }
}