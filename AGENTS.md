# Project Guidelines for AI Agents

## Project Overview

This is a Laravel/Inertia/Vue.js document scanner application with the following stack:

- **Backend**: Laravel 12.x with PHP 8.2+
- **Frontend**: Vue.js 3.x with TypeScript, Inertia.js
- **Database**: MySQL
- **Cache**: Redis
- **Search**: Typesense
- **File Storage**: Minio
- **Mail**: Mailpit
- **Development Environment**: Laravel Sail

## Code Quality Standards

### PHP Standards

- **Testing**: All tests must be written in PestPHP 4.x
- **Code Style**: All PHP code must be verified with Laravel Pint
- **Static Analysis**: PHPStan must pass with level 9
- **Magic Numbers**: PHP Magic Numbers Checker must pass (no magic numbers allowed)
- **Documentation**: All features must be documented with UK English
- **Enums**: Use PHP enums for fixed sets of values where applicable
- **Thin Controllers**: Controllers should be thin, delegating to service classes
- **Validation**: Use FormRequest classes for all validation logic
- **Responses**: Use Laravel collections and API resources for responses
- **Service Classes**: Core business logic should be in service classes

### Frontend Standards

- **Package Manager**: Bun (replaces npm)
- **Code Style**: ESLint + Prettier
- **Type Safety**: TypeScript strict mode
- **Testing**: Vitest for unit tests, Playwright for E2E tests

## Development Workflow

### Before Committing

1. Run `composer pint` to fix code style
2. Run `php artisan test` to ensure all tests pass
3. Run `phpstan analyse` to ensure static analysis passes
4. Run `php-magic-numbers-checker` to ensure no magic numbers
5. Run `bun lint` and `bun format` for frontend code
6. Update documentation for any new features

### File Organisation

- Follow PSR-4 autoloading standards
- Use Laravel's conventional directory structure
- Vue components should be in `resources/js/Components/`
- Tests should be in `tests/` with appropriate subdirectories

## Language and Documentation

- **Language**: All text, comments, and documentation must be in UK English
- **Documentation**: Every feature must have corresponding documentation
- **Comments**: Code should be self-documenting; add comments only when necessary

## Database Standards

- All migrations must be reversible
- Use proper foreign key constraints
- Index columns that are frequently queried
- Use Laravel's conventional naming (snake_case for tables, camelCase for models)

## API Standards

- Follow RESTful conventions
- Use proper HTTP status codes
- Return consistent JSON responses
- Implement proper error handling
- Use Laravel's API resource classes for data transformation

## Frontend Standards

- Use Composition API for Vue 3
- Follow TypeScript best practices
- Use Pinia for state management
- Implement proper error boundaries
- Use Tailwind CSS for styling

## Security Standards

- Validate all input data
- Sanitise output data
- Use Laravel's built-in CSRF protection
- Implement proper authentication and authorisation
- Never commit secrets or API keys

## Performance Standards

- Optimise database queries
- Implement proper caching strategies
- Use lazy loading where appropriate
- Minimise bundle size
- Use Laravel's built-in optimisation commands

## Testing Standards

- Write unit tests for business logic
- Write feature tests for user workflows
- Aim for 80%+ code coverage
- Use proper test data factories
- Test both success and failure scenarios

## Required Tools Installation

The following tools must be installed and configured:

- PHP Magic Numbers Checker
- PHPStan (level 9)
- PestPHP 4.x
- Laravel Pint
- Bun (package manager)
- ESLint + Prettier
- TypeScript

## Ambiguity Resolution

If a task is ambiguous, present the user with options rather than making assumptions. Always ask for clarification when requirements are unclear.

## Environment Configuration

- Use `.env.example` as template
- Never commit `.env` file
- Use Laravel Sail for consistent development environment
- All services (MySQL, Redis, Typesense, Minio, Mailpit) should be configured via Sail
