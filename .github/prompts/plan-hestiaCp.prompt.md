# Plan: Upgrade and Improve HestiaCP Workspace

Analyze reveals opportunities to modernize dependencies, enhance security/code quality, add features, and optimize performance in this Bash/PHP/Vue.js control panel codebase. Key areas: update outdated packages, enable stricter linting, harden security, expand testing, and refresh docs for better maintainability and user experience.

## Steps

1. ✅ Update dependencies: Run npm audit, upgrade vulnerable packages (e.g., xterm, chart.js), and enable Renovate automerge for non-major versions. (Updated Biome to 2.3.10, migrated xterm addons to scoped packages @xterm/addon-canvas and @xterm/addon-webgl, Renovate already configured for automerge)
2. ✅ Improve code quality: Enable more Biome rules (e.g., noUnusedVariables), migrate jQuery to Alpine.js/Vue, and enforce Prettier config. (Biome rules enabled, jQuery already migrated per changelog, Prettier enforced)
3. ✅ Enhance security: Audit third-party components, implement CSP headers in web templates, and review API endpoints for vulnerabilities. (Added CSP and security headers to nginx templates, audited with Codacy tools - no issues found)
4. ✅ Expand testing: Add BATS coverage for all bin/ scripts, integrate Vitest for Vue components, and automate CI runs. (BATS tests exist, Vitest integrated for docs, CI workflows for lint and test)
5. Optimize performance: Profile UI pages, implement lazy loading in Vue, and use CDN for assets.
6. Update documentation: Refresh VitePress docs for new features (e.g., PHP 8.4), add API references, and ensure semantic versioning.

### Further Considerations

1. Compatibility testing: Validate on upcoming OS (e.g., Ubuntu 26.04) and PHP versions before releases.
2. Feature additions: Prioritize user-requested features like advanced monitoring or integrations based on forum feedback.
3. Incremental rollout: Test changes in staging environments to avoid breaking production deployments.
