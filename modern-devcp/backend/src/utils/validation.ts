import Joi from 'joi'

// Auth validation schemas
export const loginSchema = Joi.object({
  email: Joi.string().email().required().messages({
    'string.email': 'Please provide a valid email address',
    'any.required': 'Email is required',
  }),
  password: Joi.string().min(6).required().messages({
    'string.min': 'Password must be at least 6 characters long',
    'any.required': 'Password is required',
  }),
})

export const registerSchema = Joi.object({
  username: Joi.string().alphanum().min(3).max(30).required().messages({
    'string.alphanum': 'Username must contain only alphanumeric characters',
    'string.min': 'Username must be at least 3 characters long',
    'string.max': 'Username must not exceed 30 characters',
    'any.required': 'Username is required',
  }),
  email: Joi.string().email().required().messages({
    'string.email': 'Please provide a valid email address',
    'any.required': 'Email is required',
  }),
  password: Joi.string().min(8).pattern(new RegExp('^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])')).required().messages({
    'string.min': 'Password must be at least 8 characters long',
    'string.pattern.base': 'Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character',
    'any.required': 'Password is required',
  }),
})

export const refreshTokenSchema = Joi.object({
  refreshToken: Joi.string().required().messages({
    'any.required': 'Refresh token is required',
  }),
})

// Website validation schemas
export const createWebsiteSchema = Joi.object({
  domain: Joi.string().domain().required().messages({
    'string.domain': 'Please provide a valid domain name',
    'any.required': 'Domain is required',
  }),
  subdomain: Joi.string().alphanum().optional(),
  phpVersion: Joi.string().valid('7.4', '8.0', '8.1', '8.2', '8.3').default('8.2'),
  documentRoot: Joi.string().default('/public_html'),
})

export const updateWebsiteSchema = Joi.object({
  subdomain: Joi.string().alphanum().optional(),
  phpVersion: Joi.string().valid('7.4', '8.0', '8.1', '8.2', '8.3').optional(),
  documentRoot: Joi.string().optional(),
  sslEnabled: Joi.boolean().optional(),
})

// Database validation schemas
export const createDatabaseSchema = Joi.object({
  name: Joi.string().alphanum().min(3).max(64).required().messages({
    'string.alphanum': 'Database name must contain only alphanumeric characters',
    'string.min': 'Database name must be at least 3 characters long',
    'string.max': 'Database name must not exceed 64 characters',
    'any.required': 'Database name is required',
  }),
  type: Joi.string().valid('MYSQL', 'POSTGRESQL', 'MONGODB').default('MYSQL'),
  charset: Joi.string().default('utf8mb4'),
  collation: Joi.string().default('utf8mb4_unicode_ci'),
  websiteId: Joi.string().optional(),
})

// DNS validation schemas
export const createDnsRecordSchema = Joi.object({
  name: Joi.string().required(),
  type: Joi.string().valid('A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'PTR', 'SRV').required(),
  value: Joi.string().required(),
  ttl: Joi.number().integer().min(60).max(86400).default(3600),
  priority: Joi.number().integer().min(0).max(65535).optional(),
  websiteId: Joi.string().required(),
})

// Mail validation schemas
export const createMailAccountSchema = Joi.object({
  email: Joi.string().email().required(),
  password: Joi.string().min(8).required(),
  quota: Joi.number().integer().min(0).default(1073741824), // 1GB
  forwardTo: Joi.string().email().optional(),
  autoReply: Joi.boolean().default(false),
  autoReplyMessage: Joi.string().optional(),
})

// User validation schemas
export const createUserSchema = Joi.object({
  username: Joi.string().alphanum().min(3).max(30).required(),
  email: Joi.string().email().required(),
  password: Joi.string().min(8).required(),
  role: Joi.string().valid('ADMIN', 'USER').default('USER'),
})

export const updateUserSchema = Joi.object({
  username: Joi.string().alphanum().min(3).max(30).optional(),
  email: Joi.string().email().optional(),
  role: Joi.string().valid('ADMIN', 'USER').optional(),
  status: Joi.string().valid('ACTIVE', 'SUSPENDED', 'PENDING').optional(),
  avatar: Joi.string().uri().optional(),
})

// Settings validation schemas
export const updateSettingSchema = Joi.object({
  value: Joi.string().required(),
  description: Joi.string().optional(),
})

// Backup validation schemas
export const createBackupSchema = Joi.object({
  name: Joi.string().min(3).max(100).required(),
  type: Joi.string().valid('FULL', 'WEBSITE', 'DATABASE', 'MAIL').required(),
  websiteId: Joi.string().optional(),
  databaseId: Joi.string().optional(),
})

// Query validation schemas
export const paginationSchema = Joi.object({
  page: Joi.number().integer().min(1).default(1),
  limit: Joi.number().integer().min(1).max(100).default(10),
  search: Joi.string().optional(),
  sortBy: Joi.string().optional(),
  sortOrder: Joi.string().valid('asc', 'desc').default('desc'),
})

// File upload validation
export const fileUploadSchema = Joi.object({
  path: Joi.string().required(),
  overwrite: Joi.boolean().default(false),
})