import { config } from 'dotenv'

// Load test environment variables
config({ path: '.env.test' })

// Set test environment
process.env.NODE_ENV = 'test'
process.env.DATABASE_URL = process.env.DATABASE_URL || 'postgresql://postgres:postgres@localhost:5432/devcp_test'
process.env.JWT_SECRET = 'test-jwt-secret'
process.env.JWT_REFRESH_SECRET = 'test-jwt-refresh-secret'

// Increase timeout for database operations
jest.setTimeout(30000)