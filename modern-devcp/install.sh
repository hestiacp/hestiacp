#!/bin/bash

# DevCP Installation Script for Ubuntu 22.04
# This script installs and configures DevCP hosting control panel

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DEVCP_USER="devcp"
DEVCP_HOME="/opt/devcp"
DEVCP_VERSION="2.0.0"
DOMAIN=""
EMAIL=""
INSTALL_SSL=false

# Logging
LOG_FILE="/var/log/devcp-install.log"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
    echo "[$(date +'%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
    echo "[ERROR] $1" >> "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
    echo "[WARNING] $1" >> "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
    echo "[INFO] $1" >> "$LOG_FILE"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root. Please use sudo."
    fi
}

# Check Ubuntu version
check_ubuntu() {
    if ! grep -q "Ubuntu 22.04" /etc/os-release; then
        warning "This script is designed for Ubuntu 22.04. Continuing anyway..."
    fi
}

# Parse command line arguments
parse_args() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --domain)
                DOMAIN="$2"
                shift 2
                ;;
            --email)
                EMAIL="$2"
                shift 2
                ;;
            --ssl)
                INSTALL_SSL=true
                shift
                ;;
            --help)
                show_help
                exit 0
                ;;
            *)
                error "Unknown option: $1"
                ;;
        esac
    done
}

show_help() {
    cat << EOF
DevCP Installation Script

Usage: $0 [OPTIONS]

Options:
    --domain DOMAIN     Set the domain name for DevCP
    --email EMAIL       Set the admin email address
    --ssl              Install SSL certificate with Let's Encrypt
    --help             Show this help message

Example:
    $0 --domain panel.example.com --email admin@example.com --ssl

EOF
}

# Update system packages
update_system() {
    log "Updating system packages..."
    apt-get update -y
    apt-get upgrade -y
    apt-get install -y curl wget git unzip software-properties-common apt-transport-https ca-certificates gnupg lsb-release
}

# Install Docker
install_docker() {
    log "Installing Docker..."
    
    # Remove old versions
    apt-get remove -y docker docker-engine docker.io containerd runc || true
    
    # Add Docker's official GPG key
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    
    # Add Docker repository
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Install Docker
    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
    
    # Start and enable Docker
    systemctl start docker
    systemctl enable docker
    
    # Add devcp user to docker group
    usermod -aG docker "$DEVCP_USER" || true
}

# Install Docker Compose
install_docker_compose() {
    log "Installing Docker Compose..."
    
    # Download and install Docker Compose
    COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep 'tag_name' | cut -d\" -f4)
    curl -L "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    
    # Create symlink
    ln -sf /usr/local/bin/docker-compose /usr/bin/docker-compose
}

# Install Node.js
install_nodejs() {
    log "Installing Node.js..."
    
    # Add NodeSource repository
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
    
    # Install global packages
    npm install -g pm2 yarn
}

# Create DevCP user
create_user() {
    log "Creating DevCP user..."
    
    if ! id "$DEVCP_USER" &>/dev/null; then
        useradd -m -s /bin/bash "$DEVCP_USER"
        usermod -aG sudo "$DEVCP_USER"
    fi
    
    # Create DevCP directory
    mkdir -p "$DEVCP_HOME"
    chown "$DEVCP_USER:$DEVCP_USER" "$DEVCP_HOME"
}

# Download and setup DevCP
setup_devcp() {
    log "Setting up DevCP..."
    
    # Clone or copy DevCP files
    if [[ -d "/tmp/devcp-source" ]]; then
        cp -r /tmp/devcp-source/* "$DEVCP_HOME/"
    else
        # Download from GitHub (if available)
        git clone https://github.com/Ghost-Dev9/DevCP.git /tmp/devcp-temp
        cp -r /tmp/devcp-temp/modern-devcp/* "$DEVCP_HOME/"
        rm -rf /tmp/devcp-temp
    fi
    
    # Set permissions
    chown -R "$DEVCP_USER:$DEVCP_USER" "$DEVCP_HOME"
    chmod +x "$DEVCP_HOME"/*.sh || true
}

# Configure environment
configure_environment() {
    log "Configuring environment..."
    
    # Generate random secrets
    JWT_SECRET=$(openssl rand -hex 32)
    JWT_REFRESH_SECRET=$(openssl rand -hex 32)
    DB_PASSWORD=$(openssl rand -hex 16)
    REDIS_PASSWORD=$(openssl rand -hex 16)
    
    # Create .env file
    cat > "$DEVCP_HOME/.env" << EOF
# DevCP Configuration
NODE_ENV=production
DOMAIN=${DOMAIN:-localhost}
ADMIN_EMAIL=${EMAIL:-admin@localhost}

# Database
DATABASE_URL=postgresql://devcp:${DB_PASSWORD}@localhost:5432/devcp?schema=public

# JWT
JWT_SECRET=${JWT_SECRET}
JWT_REFRESH_SECRET=${JWT_REFRESH_SECRET}

# Redis
REDIS_URL=redis://:${REDIS_PASSWORD}@localhost:6379

# API
VITE_API_URL=http://${DOMAIN:-localhost}/api
VITE_WS_URL=ws://${DOMAIN:-localhost}

# CORS
CORS_ORIGIN=http://${DOMAIN:-localhost}

# SSL
INSTALL_SSL=${INSTALL_SSL}
EOF
    
    chown "$DEVCP_USER:$DEVCP_USER" "$DEVCP_HOME/.env"
    chmod 600 "$DEVCP_HOME/.env"
}

# Install and configure PostgreSQL
install_postgresql() {
    log "Installing PostgreSQL..."
    
    apt-get install -y postgresql postgresql-contrib
    
    # Start and enable PostgreSQL
    systemctl start postgresql
    systemctl enable postgresql
    
    # Create database and user
    sudo -u postgres psql << EOF
CREATE USER devcp WITH PASSWORD '$(grep DATABASE_URL "$DEVCP_HOME/.env" | cut -d: -f3 | cut -d@ -f1)';
CREATE DATABASE devcp OWNER devcp;
GRANT ALL PRIVILEGES ON DATABASE devcp TO devcp;
\q
EOF
}

# Install and configure Redis
install_redis() {
    log "Installing Redis..."
    
    apt-get install -y redis-server
    
    # Configure Redis
    sed -i 's/^# requirepass.*/requirepass '"$(grep REDIS_URL "$DEVCP_HOME/.env" | cut -d: -f3 | cut -d@ -f1)"'/' /etc/redis/redis.conf
    sed -i 's/^bind 127.0.0.1.*/bind 127.0.0.1/' /etc/redis/redis.conf
    
    # Start and enable Redis
    systemctl restart redis-server
    systemctl enable redis-server
}

# Install Nginx
install_nginx() {
    log "Installing Nginx..."
    
    apt-get install -y nginx
    
    # Create DevCP site configuration
    cat > /etc/nginx/sites-available/devcp << EOF
server {
    listen 80;
    server_name ${DOMAIN:-localhost};
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Frontend
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }
    
    # API
    location /api {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_cache_bypass \$http_upgrade;
    }
    
    # WebSocket
    location /socket.io/ {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF
    
    # Enable site
    ln -sf /etc/nginx/sites-available/devcp /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test and reload Nginx
    nginx -t
    systemctl restart nginx
    systemctl enable nginx
}

# Install SSL certificate
install_ssl() {
    if [[ "$INSTALL_SSL" == "true" && -n "$DOMAIN" && -n "$EMAIL" ]]; then
        log "Installing SSL certificate..."
        
        # Install Certbot
        apt-get install -y certbot python3-certbot-nginx
        
        # Get certificate
        certbot --nginx -d "$DOMAIN" --email "$EMAIL" --agree-tos --non-interactive
        
        # Setup auto-renewal
        systemctl enable certbot.timer
    fi
}

# Build and start DevCP
start_devcp() {
    log "Building and starting DevCP..."
    
    cd "$DEVCP_HOME"
    
    # Build backend
    cd backend
    sudo -u "$DEVCP_USER" npm install
    sudo -u "$DEVCP_USER" npx prisma generate
    sudo -u "$DEVCP_USER" npx prisma db push
    sudo -u "$DEVCP_USER" npm run build
    
    # Build frontend
    cd ../frontend
    sudo -u "$DEVCP_USER" npm install
    sudo -u "$DEVCP_USER" npm run build
    
    # Start services with PM2
    cd "$DEVCP_HOME"
    sudo -u "$DEVCP_USER" pm2 start backend/dist/server.js --name "devcp-backend"
    sudo -u "$DEVCP_USER" pm2 serve frontend/dist 3000 --name "devcp-frontend" --spa
    sudo -u "$DEVCP_USER" pm2 save
    sudo -u "$DEVCP_USER" pm2 startup
}

# Configure firewall
configure_firewall() {
    log "Configuring firewall..."
    
    # Install UFW if not present
    apt-get install -y ufw
    
    # Configure UFW
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow ssh
    ufw allow 80/tcp
    ufw allow 443/tcp
    ufw --force enable
}

# Create systemd service
create_systemd_service() {
    log "Creating systemd service..."
    
    cat > /etc/systemd/system/devcp.service << EOF
[Unit]
Description=DevCP Hosting Control Panel
After=network.target postgresql.service redis-server.service

[Service]
Type=forking
User=$DEVCP_USER
WorkingDirectory=$DEVCP_HOME
ExecStart=/usr/bin/pm2 resurrect
ExecReload=/usr/bin/pm2 reload all
ExecStop=/usr/bin/pm2 kill
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF
    
    systemctl daemon-reload
    systemctl enable devcp
}

# Main installation function
main() {
    log "Starting DevCP installation..."
    
    check_root
    check_ubuntu
    parse_args "$@"
    
    # Create log file
    touch "$LOG_FILE"
    chmod 644 "$LOG_FILE"
    
    # Installation steps
    update_system
    create_user
    install_nodejs
    install_postgresql
    install_redis
    install_nginx
    setup_devcp
    configure_environment
    start_devcp
    install_ssl
    configure_firewall
    create_systemd_service
    
    log "DevCP installation completed successfully!"
    
    # Show completion message
    cat << EOF

${GREEN}╔══════════════════════════════════════════════════════════════╗
║                    DevCP Installation Complete!                  ║
╚══════════════════════════════════════════════════════════════╝${NC}

${BLUE}Access Information:${NC}
• URL: http://${DOMAIN:-localhost}
• Admin Panel: http://${DOMAIN:-localhost}/admin
• API: http://${DOMAIN:-localhost}/api

${BLUE}Default Credentials:${NC}
• Username: admin
• Password: admin (Please change immediately!)

${BLUE}Services:${NC}
• DevCP Backend: Running on port 3001
• DevCP Frontend: Running on port 3000
• PostgreSQL: Running on port 5432
• Redis: Running on port 6379
• Nginx: Running on ports 80/443

${BLUE}Management Commands:${NC}
• Start: systemctl start devcp
• Stop: systemctl stop devcp
• Restart: systemctl restart devcp
• Status: systemctl status devcp
• Logs: journalctl -u devcp -f

${YELLOW}Important:${NC}
1. Change the default admin password immediately
2. Review the configuration in $DEVCP_HOME/.env
3. Check the logs at $LOG_FILE

${GREEN}Enjoy your new DevCP hosting control panel!${NC}

EOF
}

# Run main function with all arguments
main "$@"