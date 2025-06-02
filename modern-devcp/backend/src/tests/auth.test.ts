import request from 'supertest'
import { app } from '../app'
import { PrismaClient } from '@prisma/client'
import bcrypt from 'bcryptjs'

const prisma = new PrismaClient()

describe('Authentication', () => {
  beforeAll(async () => {
    // Clean up test database
    await prisma.userSession.deleteMany()
    await prisma.user.deleteMany()
  })

  afterAll(async () => {
    await prisma.$disconnect()
  })

  describe('POST /api/auth/register', () => {
    it('should register a new user', async () => {
      const userData = {
        username: 'testuser',
        email: 'test@example.com',
        password: 'Test123!@#',
      }

      const response = await request(app)
        .post('/api/auth/register')
        .send(userData)
        .expect(201)

      expect(response.body).toHaveProperty('message', 'User registered successfully')
      expect(response.body.user).toHaveProperty('id')
      expect(response.body.user).toHaveProperty('username', userData.username)
      expect(response.body.user).toHaveProperty('email', userData.email)
      expect(response.body.user).not.toHaveProperty('password')
    })

    it('should not register user with existing email', async () => {
      const userData = {
        username: 'testuser2',
        email: 'test@example.com', // Same email as above
        password: 'Test123!@#',
      }

      const response = await request(app)
        .post('/api/auth/register')
        .send(userData)
        .expect(409)

      expect(response.body).toHaveProperty('error')
    })

    it('should validate password requirements', async () => {
      const userData = {
        username: 'testuser3',
        email: 'test3@example.com',
        password: 'weak', // Weak password
      }

      const response = await request(app)
        .post('/api/auth/register')
        .send(userData)
        .expect(400)

      expect(response.body).toHaveProperty('error', 'Validation failed')
    })
  })

  describe('POST /api/auth/login', () => {
    beforeAll(async () => {
      // Create a test user
      const hashedPassword = await bcrypt.hash('Test123!@#', 12)
      await prisma.user.create({
        data: {
          username: 'logintest',
          email: 'login@example.com',
          password: hashedPassword,
          role: 'USER',
          status: 'ACTIVE',
        },
      })
    })

    it('should login with valid credentials', async () => {
      const loginData = {
        email: 'login@example.com',
        password: 'Test123!@#',
      }

      const response = await request(app)
        .post('/api/auth/login')
        .send(loginData)
        .expect(200)

      expect(response.body).toHaveProperty('message', 'Login successful')
      expect(response.body).toHaveProperty('user')
      expect(response.body).toHaveProperty('tokens')
      expect(response.body.tokens).toHaveProperty('accessToken')
      expect(response.body.tokens).toHaveProperty('refreshToken')
    })

    it('should not login with invalid credentials', async () => {
      const loginData = {
        email: 'login@example.com',
        password: 'wrongpassword',
      }

      const response = await request(app)
        .post('/api/auth/login')
        .send(loginData)
        .expect(401)

      expect(response.body).toHaveProperty('error')
    })

    it('should not login with non-existent user', async () => {
      const loginData = {
        email: 'nonexistent@example.com',
        password: 'Test123!@#',
      }

      const response = await request(app)
        .post('/api/auth/login')
        .send(loginData)
        .expect(401)

      expect(response.body).toHaveProperty('error')
    })
  })

  describe('GET /api/auth/me', () => {
    let accessToken: string

    beforeAll(async () => {
      // Login to get access token
      const loginResponse = await request(app)
        .post('/api/auth/login')
        .send({
          email: 'login@example.com',
          password: 'Test123!@#',
        })

      accessToken = loginResponse.body.tokens.accessToken
    })

    it('should get current user with valid token', async () => {
      const response = await request(app)
        .get('/api/auth/me')
        .set('Authorization', `Bearer ${accessToken}`)
        .expect(200)

      expect(response.body).toHaveProperty('message', 'User retrieved successfully')
      expect(response.body).toHaveProperty('user')
      expect(response.body.user).toHaveProperty('email', 'login@example.com')
    })

    it('should not get user without token', async () => {
      const response = await request(app)
        .get('/api/auth/me')
        .expect(401)

      expect(response.body).toHaveProperty('error')
    })

    it('should not get user with invalid token', async () => {
      const response = await request(app)
        .get('/api/auth/me')
        .set('Authorization', 'Bearer invalid-token')
        .expect(401)

      expect(response.body).toHaveProperty('error')
    })
  })
})