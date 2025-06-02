#!/bin/bash

# DevCP Development Startup Script
# This script sets up and starts the development environment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
log() {
    echo -e "${GREEN}[$(date +'%H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check if Node.js is installed
check_nodejs() {
    if ! command -v node &> /dev/null; then
        error "Node.js is not installed. Please install Node.js 18+ and try again."
    fi
    
    NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
    if [ "$NODE_VERSION" -lt 18 ]; then
        error "Node.js version 18+ is required. Current version: $(node -v)"
    fi
    
    log "Node.js version: $(node -v) ✓"
}

# Check if PostgreSQL is running
check_postgresql() {
    if ! command -v psql &> /dev/null; then
        warning "PostgreSQL client not found. Make sure PostgreSQL is installed and running."
        return
    fi
    
    if ! pg_isready -h localhost -p 5432 &> /dev/null; then
        warning "PostgreSQL is not running on localhost:5432"
        info "You can start PostgreSQL with Docker: docker run -d --name postgres -e POSTGRES_PASSWORD=postgres -p 5432:5432 postgres:15"
        return
    fi
    
    log "PostgreSQL is running ✓"
}

# Install dependencies
install_dependencies() {
    log "Installing dependencies..."
    
    if [ ! -d "node_modules" ]; then
        npm install
    fi
    
    if [ ! -d "backend/node_modules" ]; then
        cd backend && npm install && cd ..
    fi
    
    if [ ! -d "frontend/node_modules" ]; then
        cd frontend && npm install && cd ..
    fi
    
    log "Dependencies installed ✓"
}

# Setup environment files
setup_environment() {
    log "Setting up environment files..."
    
    # Backend environment
    if [ ! -f "backend/.env" ]; then
        if [ -f "backend/.env.example" ]; then
            cp backend/.env.example backend/.env
            log "Created backend/.env from example"
        else
            cat > backend/.env << EOF
# Database
DATABASE_URL="postgresql://postgres:postgres@localhost:5432/devcp_dev?schema=public"

# JWT
JWT_SECRET="dev-jwt-secret-change-in-production"
JWT_REFRESH_SECRET="dev-jwt-refresh-secret-change-in-production"

# Server
NODE_ENV="development"
PORT=3001
HOST="localhost"

# CORS
CORS_ORIGIN="http://localhost:3000"

# Logging
LOG_LEVEL="debug"
EOF
            log "Created backend/.env with default values"
        fi
    fi
    
    # Frontend environment
    if [ ! -f "frontend/.env" ]; then
        if [ -f "frontend/.env.example" ]; then
            cp frontend/.env.example frontend/.env
            log "Created frontend/.env from example"
        else
            cat > frontend/.env << EOF
# API Configuration
VITE_API_URL=http://localhost:3001/api
VITE_WS_URL=ws://localhost:3001

# Application
VITE_APP_NAME=DevCP
VITE_APP_VERSION=2.0.0
EOF
            log "Created frontend/.env with default values"
        fi
    fi
    
    log "Environment files ready ✓"
}

# Setup database
setup_database() {
    log "Setting up database..."
    
    cd backend
    
    # Generate Prisma client
    if [ ! -d "node_modules/.prisma" ]; then
        npx prisma generate
        log "Generated Prisma client ✓"
    fi
    
    # Push database schema
    if npx prisma db push --accept-data-loss &> /dev/null; then
        log "Database schema updated ✓"
    else
        warning "Could not update database schema. Make sure PostgreSQL is running."
    fi
    
    cd ..
}

# Start development servers
start_servers() {
    log "Starting development servers..."
    
    # Check if ports are available
    if lsof -Pi :3000 -sTCP:LISTEN -t >/dev/null 2>&1; then
        warning "Port 3000 is already in use"
    fi
    
    if lsof -Pi :3001 -sTCP:LISTEN -t >/dev/null 2>&1; then
        warning "Port 3001 is already in use"
    fi
    
    # Start servers using npm script
    log "Starting backend and frontend servers..."
    info "Backend will be available at: http://localhost:3001"
    info "Frontend will be available at: http://localhost:3000"
    info "Press Ctrl+C to stop all servers"
    
    npm run dev
}

# Main function
main() {
    echo -e "${BLUE}"
    cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║                    DevCP Development Setup                   ║
╚══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    log "Starting DevCP development environment..."
    
    check_nodejs
    check_postgresql
    install_dependencies
    setup_environment
    setup_database
    start_servers
}

# Handle script interruption
trap 'echo -e "\n${YELLOW}Development servers stopped.${NC}"; exit 0' INT

# Run main function
main "$@"