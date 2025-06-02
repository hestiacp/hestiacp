#!/bin/bash

# DevCP Production Build Script
# This script builds the application for production deployment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BUILD_DIR="./dist"
BACKEND_BUILD_DIR="./backend/dist"
FRONTEND_BUILD_DIR="./frontend/dist"

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

# Clean previous builds
clean_builds() {
    log "Cleaning previous builds..."
    
    rm -rf "$BUILD_DIR"
    rm -rf "$BACKEND_BUILD_DIR"
    rm -rf "$FRONTEND_BUILD_DIR"
    
    log "Previous builds cleaned ✓"
}

# Install dependencies
install_dependencies() {
    log "Installing production dependencies..."
    
    # Root dependencies
    npm ci --only=production
    
    # Backend dependencies
    cd backend
    npm ci --only=production
    cd ..
    
    # Frontend dependencies
    cd frontend
    npm ci
    cd ..
    
    log "Dependencies installed ✓"
}

# Build backend
build_backend() {
    log "Building backend..."
    
    cd backend
    
    # Generate Prisma client
    npx prisma generate
    
    # Type check
    npm run type-check
    
    # Build TypeScript
    npm run build
    
    cd ..
    
    log "Backend built successfully ✓"
}

# Build frontend
build_frontend() {
    log "Building frontend..."
    
    cd frontend
    
    # Type check
    npm run type-check
    
    # Build React app
    npm run build
    
    cd ..
    
    log "Frontend built successfully ✓"
}

# Create production package
create_package() {
    log "Creating production package..."
    
    mkdir -p "$BUILD_DIR"
    
    # Copy backend build
    cp -r "$BACKEND_BUILD_DIR" "$BUILD_DIR/backend"
    
    # Copy frontend build
    cp -r "$FRONTEND_BUILD_DIR" "$BUILD_DIR/frontend"
    
    # Copy configuration files
    cp package.json "$BUILD_DIR/"
    cp ecosystem.config.js "$BUILD_DIR/"
    cp docker-compose.yml "$BUILD_DIR/"
    cp -r nginx "$BUILD_DIR/"
    
    # Copy backend package.json and node_modules
    cp backend/package.json "$BUILD_DIR/backend/"
    cp -r backend/node_modules "$BUILD_DIR/backend/"
    cp -r backend/prisma "$BUILD_DIR/backend/"
    
    # Create startup script
    cat > "$BUILD_DIR/start.sh" << 'EOF'
#!/bin/bash
set -e

echo "Starting DevCP Production..."

# Start backend
cd backend
node server.js &
BACKEND_PID=$!

# Start frontend (using serve)
cd ../frontend
npx serve -s . -l 3000 &
FRONTEND_PID=$!

echo "DevCP started successfully!"
echo "Backend PID: $BACKEND_PID"
echo "Frontend PID: $FRONTEND_PID"
echo "Frontend: http://localhost:3000"
echo "Backend: http://localhost:3001"

# Wait for processes
wait $BACKEND_PID $FRONTEND_PID
EOF
    
    chmod +x "$BUILD_DIR/start.sh"
    
    log "Production package created ✓"
}

# Run tests before building
run_tests() {
    log "Running tests..."
    
    # Backend tests
    cd backend
    if npm test &> /dev/null; then
        log "Backend tests passed ✓"
    else
        warning "Backend tests failed or not configured"
    fi
    cd ..
    
    # Frontend tests
    cd frontend
    if npm test -- --watchAll=false &> /dev/null; then
        log "Frontend tests passed ✓"
    else
        warning "Frontend tests failed or not configured"
    fi
    cd ..
}

# Optimize build
optimize_build() {
    log "Optimizing build..."
    
    # Remove development dependencies from backend
    cd "$BUILD_DIR/backend"
    npm prune --production
    cd ../..
    
    # Remove source maps from frontend (optional)
    find "$BUILD_DIR/frontend" -name "*.map" -delete
    
    log "Build optimized ✓"
}

# Create archive
create_archive() {
    log "Creating deployment archive..."
    
    tar -czf "devcp-production-$(date +%Y%m%d-%H%M%S).tar.gz" -C "$BUILD_DIR" .
    
    log "Deployment archive created ✓"
}

# Display build info
display_info() {
    echo -e "${BLUE}"
    cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║                    Build Complete!                           ║
╚══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    info "Build directory: $BUILD_DIR"
    info "Backend build: $BUILD_DIR/backend"
    info "Frontend build: $BUILD_DIR/frontend"
    
    if [ -f "devcp-production-"*.tar.gz ]; then
        ARCHIVE=$(ls -t devcp-production-*.tar.gz | head -1)
        info "Deployment archive: $ARCHIVE"
    fi
    
    echo -e "${GREEN}"
    cat << "EOF"

Deployment Instructions:
1. Copy the build directory or archive to your production server
2. Install Node.js 18+ and PostgreSQL on the server
3. Run the installation script: ./install.sh
4. Or start manually: ./start.sh

Docker Deployment:
1. Copy docker-compose.yml and nginx/ to your server
2. Run: docker-compose up -d

PM2 Deployment:
1. Install PM2: npm install -g pm2
2. Run: pm2 start ecosystem.config.js
EOF
    echo -e "${NC}"
}

# Main function
main() {
    echo -e "${BLUE}"
    cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║                    DevCP Production Build                    ║
╚══════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    log "Starting production build..."
    
    clean_builds
    install_dependencies
    run_tests
    build_backend
    build_frontend
    create_package
    optimize_build
    create_archive
    display_info
    
    log "Production build completed successfully! 🚀"
}

# Handle script interruption
trap 'echo -e "\n${YELLOW}Build interrupted.${NC}"; exit 1' INT

# Run main function
main "$@"