# Backend CLAUDE.md

PHP backend using FrankenPHP, League Container DI, MariaDB ORM.

## Commands

```bash
vendor/bin/phpunit                      # Run all tests
vendor/bin/phpunit tests/path/Test.php  # Single test file
vendor/bin/phpstan analyze              # Static analysis (level max)
bin/console migration:run               # Run migrations
bin/console migration:generate          # Generate new migration
bin/console cache:clear                 # Clear cache
```

## Structure (`/backend/src`)

- **Controller/**: HTTP endpoints with attribute-based routing (`#[RouteGet]`, `#[RoutePost]`)
- **Dto/**: Data Transfer Objects for API contracts
- **Model/Entity/**: ORM entities with attribute-based mapping
- **Model/Repository/**: Database access layer
- **Service/Provider/**: Business logic services (cached data retrieval)
- **Service/Authentication/**: JWT authentication
- **Command/**: CLI commands (migrations, data updates, warmup)
- **Route/Routes.php**: Enum defining all API routes

## Key Patterns

- Controllers receive `ServerRequestInterface`, return `ResponseInterface`
- Services injected via constructor (League Container DI)
- DTOs have static `fromEntity()` factory methods
- Entities use `DateTimeImmutable` with `Type::Timestamp` for datetime fields
- Financial values use php-decimal for precision (`Decimal` serialises as JSON string)

## Database Migrations

Files in `/backend/migrations/`, naming: `YYYYMMDD_HHMMSS_Description.php`

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

## Code Quality

- PHPStan max level, cognitive complexity limit 15
- Strict typing (PHP 8.5+)
