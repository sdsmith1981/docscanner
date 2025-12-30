# Document Scanner

A modern Laravel/Inertia/Vue.js document scanner application with MySQL, Redis, Typesense, Minio, and Mailpit.

## Tech Stack

- **Backend**: Laravel 12.x with PHP 8.2+
- **Frontend**: Vue.js 3.x with TypeScript, Inertia.js
- **Database**: MySQL
- **Cache**: Redis
- **Search**: Typesense
- **File Storage**: Minio
- **Mail**: Mailpit
- **Package Manager**: Bun (frontend), Composer (backend)
- **Development Environment**: Laravel Sail

## Features

- 📄 Document scanning and processing
- 🔍 Full-text search with Typesense
- 📊 Document organisation and categorisation
- 👥 User authentication and authorisation
- 🎨 Modern responsive UI with Tailwind CSS
- ⚡ Real-time updates with Laravel WebSockets
- 📱 Mobile-friendly responsive design
- 🔒 Security best practices
- 🧪 Comprehensive test coverage

## Development Setup

### Prerequisites

- Docker & Docker Compose
- PHP 8.2+
- Bun (package manager)
- Composer

### Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/sdsmith1981/docscanner.git
    cd docscanner
    ```

2. **Install dependencies**

    ```bash
    composer install
    bun install
    ```

3. **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Start development environment**
    ```bash
    ./vendor/bin/sail up -d
    ```
5. **Run migrations and seeders**

    ```bash
    ./vendor/bin/sail artisan migrate
    ./vendor/bin/sail artisan db:seed
    ```

6. **Install frontend dependencies and build**

    ```bash
    bun install
    bun run build
    ```

7. **Access the application**
    - Application: http://localhost
    - Mailpit (email testing): http://localhost:8025
    - Minio (file storage): http://localhost:9001

## Development Commands

### Backend

```bash
# Run tests
php artisan test

# Code quality checks
composer quality

# Laravel Sail commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker
```

### Frontend

```bash
# Development server
bun run dev

# Build for production
bun run build

# Code quality
bun lint
bun format
```

### Development Servers

```bash
# Full development stack
composer dev

# Development with SSR
composer dev:ssr
```

## Project Structure

```
docscanner/
├── app/                    # Laravel application code
│   ├── Http/
│   │   ├── Controllers/     # API controllers (thin)
│   │   ├── Requests/       # FormRequest validation
│   │   └── Resources/      # API resource responses
│   ├── Models/             # Eloquent models
│   ├── Services/           # Core business logic
│   └── Enums/              # PHP enums
├── resources/js/           # Vue.js frontend
│   ├── Components/         # Vue components
│   ├── Pages/            # Page components
│   ├── Types/            # TypeScript definitions
│   └── Stores/           # Pinia stores
├── database/             # Database files
│   ├── migrations/       # Database migrations
│   └── factories/       # Test data factories
├── tests/               # Test files
│   ├── Feature/          # Feature tests
│   └── Unit/            # Unit tests
├── skills/              # AI agent skill definitions
└── docs/               # Project documentation
```

## Code Quality Standards

### Backend

- **Testing**: PestPHP 4.x with 80%+ coverage
- **Code Style**: Laravel Pint
- **Static Analysis**: PHPStan level 9
- **Magic Numbers**: Not allowed (checked with custom script)
- **Architecture**: Thin controllers, service classes, FormRequest validation
- **Enums**: Use PHP enums for fixed value sets

### Frontend

- **Language**: TypeScript (strict mode)
- **Package Manager**: Bun
- **Code Style**: ESLint + Prettier
- **Testing**: Vitest (unit), Cypress (E2E)
- **Framework**: Vue 3 Composition API

### Documentation

- **Language**: UK English
- **Format**: Markdown
- **Templates**: Provided for consistency
- **Coverage**: All features documented

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=UserTest

# Run with coverage
php artisan test --coverage

# Frontend tests
bun test

# E2E tests
bun run test:e2e
```

## Deployment

### Environment Variables

```env
APP_NAME="Document Scanner"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=docscanner
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

SEARCH_ENGINE=typesense
TYPESENSE_HOST=typesense
TYPESENSE_PORT=8107
TYPESENSE_API_KEY=your-api-key

FILESYSTEM_DISK=local
FILESYSTEM_CLOUD=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=docscanner
AWS_ENDPOINT=http://minio:9000
```

### Build Commands

```bash
# Optimise for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build frontend assets
bun run build

# Run migrations
php artisan migrate --force
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run quality checks: `composer quality`
5. Submit a pull request

### Quality Checklist

- [ ] All tests pass
- [ ] Code style is compliant
- [ ] No magic numbers
- [ ] Documentation updated
- [ ] TypeScript compilation succeeds

## Security

- All input data is validated
- CSRF protection enabled
- Proper authentication and authorisation
- Secure file uploads
- Environment variables not committed
- Regular security updates

## Performance

- Database query optimisation
- Redis caching strategies
- Lazy loading where appropriate
- Minimised bundle sizes
- CDN for static assets

## Support

For support and questions:

- Create an issue on GitHub
- Check documentation in `/docs`
- Review skill files in `/skills`

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

**Built with ❤️ using Laravel, Vue.js, and modern web technologies.**
