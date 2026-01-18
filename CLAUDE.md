# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FinGather is a portfolio tracking application with PHP backend (Spiral RoadRunner), Angular frontend, and MariaDB database. Uses memcached and Redis for caching, RabbitMQ for async job processing.

## Build & Development Commands

### Docker
```bash
docker compose up -d --build              # Full stack
docker compose --profile dev up -d        # With DB admin tools (Adminer, Buggregator)
```

### Frontend (in /frontend)
```bash
pnpm run start       # Dev server with SSL on localhost
pnpm run build-prod  # Production build
pnpm run lint        # ESLint + Stylelint
pnpm run lint-fix    # Auto-fix lint issues
pnpm run test        # Karma/Jasmine tests
```

### Backend (in /backend)
```bash
vendor/bin/phpunit                    # Run tests
vendor/bin/phpunit tests/path/Test.php  # Single test file
vendor/bin/phpstan analyze            # Static analysis (level max)
bin/console migration:run             # Run migrations
bin/console migration:generate        # Generate new migration
bin/console cache:clear               # Clear cache
```

## Architecture

### Backend Structure (/backend/src)
- **Controller/**: HTTP endpoints with attribute-based routing (`#[RouteGet]`, `#[RoutePost]`)
- **Dto/**: Data Transfer Objects for API contracts
- **Model/Entity/**: ORM entities with attribute-based mapping
- **Model/Repository/**: Database access layer
- **Service/Provider/**: Business logic services (cached data retrieval)
- **Service/Authentication/**: JWT authentication
- **Command/**: CLI commands (migrations, data updates, warmup)
- **Route/Routes.php**: Enum defining all API routes

### Frontend Structure (/frontend/src/app)
- **models/**: TypeScript interfaces matching backend DTOs
- **services/**: HTTP services for each domain entity
- Feature modules: assets, portfolios, groups, transactions, dividends, users, etc.
- Uses Angular 21 standalone components

### Key Patterns

**Backend:**
- Controllers receive `ServerRequestInterface`, return `ResponseInterface`
- Services injected via constructor (League Container DI)
- DTOs have static `fromEntity()` factory methods
- Entities use `DateTimeImmutable` with `Type::Timestamp` for datetime fields
- Financial values use php-decimal for precision

**Frontend:**
- Services extend pattern with `async` methods returning promises
- Models mirror backend DTOs
- i18n via @ngx-translate (`{{ 'key' | translate }}`)
- Date formatting: `| date: 'y-MM-dd'` for dates, `| date: 'HH:mm'` for time

### Database Migrations

Migrations in `/backend/migrations/` follow naming: `YYYYMMDD_HHMMSS_Description.php`

```php
final class ExampleMigration extends Migration
{
    public function up(): void
    {
        $this->table('table_name')
            ->addColumn('column', Type::Timestamp, nullable: true)
            ->alter();
    }

    public function down(): void
    {
        $this->table('table_name')
            ->dropColumn('column')
            ->alter();
    }
}
```

## External APIs

- **TwelveData**: Stock/crypto data (required API key in `.env`)
- **OpenFIGI**: Financial instrument identification
- **Trading212**: Broker API integration

## Code Quality

- Backend: PHPStan max level, cognitive complexity limit 15
- Frontend: ESLint + Stylelint with SCSS guidelines
- Both enforce strict typing (PHP 8.5+, TypeScript strict mode)