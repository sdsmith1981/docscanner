# Project Structure and Skills

This document outlines the project structure and available AI agent skills for the Laravel/Inertia/Vue.js document scanner application.

## Project Structure

```
docscanner/
├── AGENTS.md                    # Main guidelines for AI agents
├── phpstan.neon                 # PHPStan configuration (level 9)
├── php-magic-numbers-checker    # Custom magic numbers checker script
├── composer.json                # PHP dependencies and scripts
├── package.json                 # Frontend dependencies (uses Bun)
├── compose.yaml                 # Laravel Sail configuration
├──
├── skills/                      # AI Agent skill definitions
│   ├── laravel-backend.md       # Laravel backend development
│   ├── vue-frontend.md          # Vue.js frontend development
│   ├── testing.md               # Testing strategies and tools
│   ├── documentation.md         # Documentation standards
│   └── database.md              # Database design and migrations
├──
├── docs/                        # Project documentation
│   └── features/                # Feature documentation
│       └── feature-template.md  # Template for new features
├──
├── app/                         # Laravel application code
├── resources/                   # Frontend assets (Vue.js)
├── database/                    # Database files
├── routes/                      # Route definitions
├── tests/                       # Test files (PestPHP)
└── vendor/                      # Composer dependencies
```

## Available Skills

### 1. Laravel Backend Development

**File:** `skills/laravel-backend.md`

**Covers:**

- Model and migration creation
- API endpoint development
- Business logic implementation
- Testing with PestPHP
- Code quality standards

**Key Requirements:**

- PHPStan level 9 compliance
- No magic numbers
- Laravel Pint formatting
- Comprehensive testing

### 2. Vue.js Frontend Development

**File:** `skills/vue-frontend.md`

**Covers:**

- Vue 3 Composition API
- TypeScript strict mode
- Tailwind CSS styling
- Component development
- State management with Pinia

**Key Requirements:**

- Bun package manager
- ESLint + Prettier formatting
- TypeScript compilation
- Responsive design

### 3. Testing

**File:** `skills/testing.md`

**Covers:**

- PestPHP backend testing
- Vitest frontend unit testing
- Cypress E2E testing
- Test-driven development
- Coverage requirements

**Key Requirements:**

- 80%+ code coverage
- Test isolation
- Proper test data
- CI/CD integration

### 4. Documentation

**File:** `skills/documentation.md`

**Covers:**

- Feature documentation
- API documentation
- Component documentation
- UK English standards
- Markdown formatting

**Key Requirements:**

- UK English only
- Comprehensive coverage
- Regular updates
- Template usage

### 5. Database Development

**File:** `skills/database.md`

**Covers:**

- Migration development
- Model relationships
- Query optimisation
- Database design
- Seeding and factories

**Key Requirements:**

- Reversible migrations
- Proper indexing
- Foreign key constraints
- Naming conventions

## Development Workflow

### Before Committing

1. **Backend Quality Check:**

    ```bash
    composer pint
    phpstan analyse
    ./php-magic-numbers-checker
    php artisan test
    ```

2. **Frontend Quality Check:**

    ```bash
    bun lint
    bun format
    bun run build
    ```

3. **Full Quality Check:**
    ```bash
    composer quality
    ```

### Available Scripts

#### Composer Scripts

- `composer setup` - Initial project setup
- `composer dev` - Development server with all services
- `composer dev:ssr` - Development with SSR
- `composer test` - Run all tests
- `composer quality` - Run all quality checks
- `composer quality-check` - Run quality checks without fixing

#### Bun Scripts

- `bun install` - Install frontend dependencies
- `bun run dev` - Frontend development server
- `bun run build` - Build for production
- `bun lint` - Lint frontend code
- `bun format` - Format frontend code

## Quality Standards

### Code Quality

- **PHPStan:** Level 9 (strictest)
- **Laravel Pint:** Code style enforcement
- **Magic Numbers:** Not allowed
- **TypeScript:** Strict mode
- **ESLint:** Frontend linting
- **Prettier:** Code formatting

### Testing

- **Backend:** PestPHP 4.x
- **Frontend Unit:** Vitest
- **E2E:** Cypress
- **Coverage:** 80%+ required

### Documentation

- **Language:** UK English only
- **Format:** Markdown
- **Templates:** Provided for consistency
- **Updates:** Required for all features

## Environment

### Development Environment

- **Laravel Sail:** Docker-based development
- **Services:** MySQL, Redis, Typesense, Minio, Mailpit
- **PHP:** 8.2+
- **Node.js:** Latest (via Bun)
- **Database:** MySQL

### Package Managers

- **PHP:** Composer
- **Frontend:** Bun (replaces npm)

## AI Agent Guidelines

### When Starting a Task

1. Read the relevant skill file(s)
2. Check AGENTS.md for project standards
3. Ask for clarification if requirements are ambiguous
4. Follow the quality checklist before completing

### When Completing a Task

1. Run all quality checks
2. Update documentation
3. Ensure tests pass
4. Follow commit message standards

### Ambiguity Resolution

- Present options rather than making assumptions
- Ask for clarification on unclear requirements
- Provide recommendations with reasoning
- Document decisions made

## Feature Development

### New Feature Template

Use `docs/features/feature-template.md` as a starting point for all new features.

### Documentation Requirements

- Every feature must have documentation
- API endpoints must be documented
- Components must be documented
- Usage examples must be provided

### Testing Requirements

- Unit tests for business logic
- Feature tests for user workflows
- Component tests for UI elements
- E2E tests for critical paths

## Security and Performance

### Security

- Validate all input data
- Sanitise output data
- Use CSRF protection
- Implement proper authentication
- Never commit secrets

### Performance

- Optimise database queries
- Implement caching strategies
- Use lazy loading
- Minimise bundle size
- Monitor query performance

## Continuous Integration

### Quality Gates

- All tests must pass
- Code style must be compliant
- Static analysis must pass
- No magic numbers allowed
- Documentation must be updated

### Deployment

- Use proper environment configuration
- Run database migrations
- Build frontend assets
- Clear caches
- Monitor deployment health
