import { Request, Response } from 'express'
import bcrypt from 'bcryptjs'
import jwt from 'jsonwebtoken'
import { PrismaClient } from '@prisma/client'
import { config } from '@/config/config'
import { logger } from '@/utils/logger'
import { asyncHandler, createError } from '@/middleware/errorHandler'

const prisma = new PrismaClient()

export class AuthController {
  public login = asyncHandler(async (req: Request, res: Response) => {
    const { email, password } = req.body

    // Find user by email
    const user = await prisma.user.findUnique({
      where: { email },
      select: {
        id: true,
        username: true,
        email: true,
        password: true,
        role: true,
        status: true,
      },
    })

    if (!user) {
      throw createError('Invalid credentials', 401)
    }

    if (user.status !== 'ACTIVE') {
      throw createError('Account is suspended or pending activation', 403)
    }

    // Verify password
    const isPasswordValid = await bcrypt.compare(password, user.password)
    if (!isPasswordValid) {
      throw createError('Invalid credentials', 401)
    }

    // Generate tokens
    const accessToken = jwt.sign(
      {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role,
      },
      config.jwt.secret,
      { expiresIn: config.jwt.expiresIn }
    )

    const refreshToken = jwt.sign(
      { id: user.id },
      config.jwt.refreshSecret,
      { expiresIn: config.jwt.refreshExpiresIn }
    )

    // Save refresh token to database
    await prisma.userSession.create({
      data: {
        userId: user.id,
        refreshToken,
        expiresAt: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000), // 30 days
      },
    })

    // Update last login
    await prisma.user.update({
      where: { id: user.id },
      data: { lastLogin: new Date() },
    })

    logger.info(`User logged in: ${user.email}`)

    res.json({
      message: 'Login successful',
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role,
      },
      tokens: {
        accessToken,
        refreshToken,
      },
    })
  })

  public register = asyncHandler(async (req: Request, res: Response) => {
    const { username, email, password } = req.body

    // Check if user already exists
    const existingUser = await prisma.user.findFirst({
      where: {
        OR: [{ email }, { username }],
      },
    })

    if (existingUser) {
      throw createError('User with this email or username already exists', 409)
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, config.security.bcryptRounds)

    // Create user
    const user = await prisma.user.create({
      data: {
        username,
        email,
        password: hashedPassword,
        role: 'USER',
        status: 'ACTIVE',
      },
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        createdAt: true,
      },
    })

    logger.info(`New user registered: ${user.email}`)

    res.status(201).json({
      message: 'User registered successfully',
      user,
    })
  })

  public refreshToken = asyncHandler(async (req: Request, res: Response) => {
    const { refreshToken } = req.body

    if (!refreshToken) {
      throw createError('Refresh token is required', 400)
    }

    // Verify refresh token
    let decoded: any
    try {
      decoded = jwt.verify(refreshToken, config.jwt.refreshSecret)
    } catch (error) {
      throw createError('Invalid refresh token', 401)
    }

    // Check if refresh token exists in database
    const session = await prisma.userSession.findUnique({
      where: { refreshToken },
      include: { user: true },
    })

    if (!session || session.expiresAt < new Date()) {
      throw createError('Refresh token expired or invalid', 401)
    }

    // Generate new access token
    const accessToken = jwt.sign(
      {
        id: session.user.id,
        username: session.user.username,
        email: session.user.email,
        role: session.user.role,
      },
      config.jwt.secret,
      { expiresIn: config.jwt.expiresIn }
    )

    res.json({
      message: 'Token refreshed successfully',
      accessToken,
    })
  })

  public logout = asyncHandler(async (req: Request, res: Response) => {
    const { refreshToken } = req.body

    if (refreshToken) {
      // Remove refresh token from database
      await prisma.userSession.deleteMany({
        where: { refreshToken },
      })
    }

    res.json({
      message: 'Logout successful',
    })
  })

  public getCurrentUser = asyncHandler(async (req: Request, res: Response) => {
    const authHeader = req.headers.authorization
    
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      throw createError('Access token is required', 401)
    }

    const token = authHeader.substring(7)
    
    let decoded: any
    try {
      decoded = jwt.verify(token, config.jwt.secret)
    } catch (error) {
      throw createError('Invalid access token', 401)
    }

    const user = await prisma.user.findUnique({
      where: { id: decoded.id },
      select: {
        id: true,
        username: true,
        email: true,
        role: true,
        status: true,
        avatar: true,
        createdAt: true,
        lastLogin: true,
      },
    })

    if (!user) {
      throw createError('User not found', 404)
    }

    res.json({
      message: 'User retrieved successfully',
      user,
    })
  })
}