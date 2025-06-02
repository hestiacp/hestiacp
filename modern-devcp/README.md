# DevCP v2.0 - Modern Hosting Control Panel

<div align="center">

![DevCP Logo](https://via.placeholder.com/200x100/3B82F6/FFFFFF?text=DevCP)

[![CI/CD](https://github.com/Ghost-Dev9/DevCP/actions/workflows/ci.yml/badge.svg)](https://github.com/Ghost-Dev9/DevCP/actions/workflows/ci.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Version](https://img.shields.io/badge/version-2.0.0-green.svg)](package.json)
[![Node.js](https://img.shields.io/badge/node.js-18+-brightgreen.svg)](https://nodejs.org/)
[![React](https://img.shields.io/badge/react-18+-blue.svg)](https://reactjs.org/)

**A modern, powerful, and user-friendly hosting control panel built with React, Node.js, and TypeScript.**

[Features](#features) • [Installation](#installation) • [Documentation](#documentation) • [Contributing](#contributing)

</div>

## 🚀 Features

DevCP v2.0 est une refonte complète du panneau de contrôle d'hébergement avec une architecture moderne, modulaire et scalable.

### 🏗️ Architecture

```
modern-devcp/
├── frontend/          # React + TypeScript + Tailwind
├── backend/           # Node.js + Express + TypeScript
├── shared/            # Types et utilitaires partagés
├── scripts/           # Scripts d'installation et déploiement
├── docker/            # Configuration Docker
├── docs/              # Documentation
└── tests/             # Tests automatisés
```

### ✨ Fonctionnalités

- 🎨 **Interface moderne** : React + Tailwind CSS responsive
- 🌙 **Mode sombre/clair** : Thème adaptatif
- 📊 **Dashboard dynamique** : Graphiques temps réel (CPU, RAM, DISK)
- 🔐 **API sécurisée** : JWT/OAuth2 + rate limiting
- 🏗️ **Architecture modulaire** : Microservices ready
- 🚀 **Performance optimisée** : Cache Redis + optimisations
- 📝 **Logs centralisés** : Winston + structured logging
- 🧪 **Tests automatisés** : Jest + Cypress
- 🔄 **CI/CD** : GitHub Actions

### 🛠️ Technologies

**Frontend:**
- React 18 + TypeScript
- Tailwind CSS + Headless UI
- Vite (build tool)
- React Query (state management)
- Chart.js/Recharts (graphiques)
- React Hook Form (formulaires)

**Backend:**
- Node.js + Express + TypeScript
- Prisma ORM + PostgreSQL
- Redis (cache & sessions)
- JWT + Passport.js
- Winston (logging)
- Helmet (sécurité)

**DevOps:**
- Docker + Docker Compose
- GitHub Actions (CI/CD)
- Nginx (reverse proxy)
- PM2 (process manager)

### 🚀 Installation Rapide

```bash
# Installation automatique Ubuntu 22.04
curl -fsSL https://raw.githubusercontent.com/Ghost-Dev9/DevCP/modern-devcp-v2/scripts/install.sh | bash

# Ou installation manuelle
git clone https://github.com/Ghost-Dev9/DevCP.git
cd DevCP/modern-devcp
./scripts/setup.sh
```

### 📖 Documentation

- [Guide d'installation](./docs/installation.md)
- [API Documentation](./docs/api.md)
- [Guide développeur](./docs/development.md)
- [Architecture](./docs/architecture.md)

### 🤝 Contribution

Voir [CONTRIBUTING.md](../CONTRIBUTING.md) pour les guidelines de contribution.

### 📄 License

GPL v3 - Voir [LICENSE](../LICENSE)