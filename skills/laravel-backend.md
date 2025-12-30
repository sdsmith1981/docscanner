# Laravel Backend Development Skill

## Overview

This skill covers Laravel backend development tasks including model creation, API endpoints, migrations, and business logic.

## Prerequisites

- Laravel 12.x knowledge
- PHP 8.2+ knowledge
- PestPHP testing framework
- Laravel Pint for code style
- PHPStan level 9 for static analysis

## Task Types

### Model and Migration Creation

- Create Eloquent models with proper relationships
- Write reversible migrations with proper foreign keys
- Add indexes for frequently queried columns
- Follow Laravel naming conventions (snake_case tables, camelCase models)

### API Development

- Create RESTful API endpoints
- Implement proper HTTP status codes
- Use Laravel collections and API resources for responses
- Use FormRequest classes for all validation logic
- Add proper error handling
- Follow UK English for all messages and documentation

### Business Logic

- Core business logic should be in service classes
- Keep controllers thin - delegate to service classes
- Use PHP enums for fixed sets of values where applicable
- Use Laravel's dependency injection
- Write comprehensive unit tests with PestPHP
- Ensure code passes PHPStan level 9 analysis
- Avoid magic numbers (use constants or config values)

### Testing Requirements

- Write unit tests for all business logic
- Write feature tests for API endpoints
- Test both success and failure scenarios
- Use proper test data factories
- Aim for 80%+ code coverage

## Quality Checklist

Before completing any backend task:

- [ ] Code passes `composer pint`
- [ ] Code passes `phpstan analyse`
- [ ] No magic numbers (`php-magic-numbers-checker`)
- [ ] All tests pass (`php artisan test`)
- [ ] Documentation is updated (UK English)
- [ ] Security best practices are followed

## File Structure

```

app/
├── Models/
├── Http/Controllers/Api/
├── Http/Requests/          # FormRequest classes for validation
├── Http/Resources/          # API resource classes for responses
├── Services/               # Core business logic
├── Enums/                  # PHP enums for fixed value sets
└── Rules/

database/
├── migrations/
└── factories/

tests/
├── Unit/
├── Feature/
└── Pest.php
```

app/
├── Models/
├── Http/Controllers/Api/
├── Http/Resources/
├── Services/
└── Rules/

database/
├── migrations/
└── factories/

tests/
├── Unit/
├── Feature/
└── Pest.php

````

## Example Commands

```bash
# Create model with migration and factory
php artisan make:model ModelName -mf

# Run tests
php artisan test

# Fix code style
composer pint

# Static analysis
phpstan analyse

# Check magic numbers
php-magic-numbers-checker
````

## Common Patterns

- Use FormRequest classes for validation
- Implement proper error responses using API resources
- Use Laravel collections for data manipulation
- Use PHP enums for fixed value sets
- Keep controllers thin - delegate to service classes
- Use Laravel's built-in authentication and authorisation
- Follow PSR-4 autoloading standards
- Use UK English for all user-facing text
