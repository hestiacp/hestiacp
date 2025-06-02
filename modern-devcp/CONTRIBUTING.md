# Contributing to DevCP

We love your input! We want to make contributing to DevCP as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

### Pull Requests

1. Fork the repo and create your branch from `main`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints.
6. Issue that pull request!

### Development Setup

1. **Fork and clone the repository**
```bash
git clone https://github.com/your-username/DevCP.git
cd DevCP/modern-devcp
```

2. **Install dependencies**
```bash
npm run install:all
```

3. **Setup environment**
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
# Edit the .env files with your configuration
```

4. **Setup database**
```bash
npm run db:generate
npm run db:push
npm run db:seed
```

5. **Start development servers**
```bash
npm run dev
```

### Code Style

We use ESLint and Prettier to maintain code quality and consistency.

- **ESLint**: For code linting
- **Prettier**: For code formatting
- **TypeScript**: For type safety
- **Conventional Commits**: For commit messages

#### Running linters
```bash
npm run lint
npm run lint:fix
npm run type-check
```

### Commit Messages

We follow the [Conventional Commits](https://conventionalcommits.org/) specification:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

Types:
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools

Examples:
```
feat(auth): add OAuth2 authentication
fix(dashboard): resolve memory leak in metrics component
docs(api): update authentication endpoints documentation
```

### Testing

We maintain high test coverage and require tests for new features.

#### Running tests
```bash
# All tests
npm test

# Backend tests only
npm run test:backend

# Frontend tests only
npm run test:frontend

# Watch mode
npm run test:watch

# Coverage report
npm run test:coverage
```

#### Test Structure

**Backend Tests:**
- Unit tests for services and utilities
- Integration tests for API endpoints
- Database tests with test database

**Frontend Tests:**
- Component tests with React Testing Library
- Hook tests
- Utility function tests

### Documentation

- Update README.md if you change functionality
- Add JSDoc comments for new functions/classes
- Update API documentation for endpoint changes
- Include examples in documentation

### Issue Reporting

We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/Ghost-Dev9/DevCP/issues/new).

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

### Feature Requests

We welcome feature requests! Please:

1. Check if the feature has already been requested
2. Provide a clear description of the feature
3. Explain why this feature would be useful
4. Consider providing a basic implementation plan

### Security Issues

Please do not report security vulnerabilities through public GitHub issues. Instead, please send an email to security@devcp.dev.

### Code Review Process

1. All submissions require review before merging
2. We may ask for changes before accepting
3. We'll do our best to respond to PRs promptly
4. After feedback has been given, we expect responses within two weeks

### Branch Naming

Use descriptive branch names:
- `feature/add-oauth-authentication`
- `fix/dashboard-memory-leak`
- `docs/update-api-documentation`
- `refactor/user-service-cleanup`

### Development Guidelines

#### Backend (Node.js/TypeScript)

- Use TypeScript for all new code
- Follow RESTful API conventions
- Use Prisma for database operations
- Implement proper error handling
- Add logging for important operations
- Use middleware for cross-cutting concerns

#### Frontend (React/TypeScript)

- Use functional components with hooks
- Implement proper TypeScript types
- Use Tailwind CSS for styling
- Follow React best practices
- Implement proper error boundaries
- Use React Query for API calls

#### Database

- Use Prisma migrations for schema changes
- Follow naming conventions (camelCase for fields)
- Add proper indexes for performance
- Include proper constraints and validations

### Performance Guidelines

- Optimize database queries
- Implement proper caching strategies
- Use lazy loading where appropriate
- Minimize bundle sizes
- Implement proper error handling

### Accessibility

- Follow WCAG 2.1 guidelines
- Use semantic HTML
- Implement proper ARIA labels
- Ensure keyboard navigation works
- Test with screen readers

### Browser Support

We support:
- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)

### License

By contributing, you agree that your contributions will be licensed under the MIT License.

### Questions?

Feel free to reach out:
- GitHub Discussions: [DevCP Discussions](https://github.com/Ghost-Dev9/DevCP/discussions)
- Discord: [DevCP Community](https://discord.gg/devcp)
- Email: contribute@devcp.dev

Thank you for contributing to DevCP! 🚀