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

## ORM (`marekskopal/orm` 2.x)

- `fetchAll()`/`findAll()` return `list<T>`; repository and provider list methods return arrays (no `Iterator`/`iterator_to_array()`). OneToMany relations are typed `Collection` (`MarekSkopal\ORM\Mapper\Collection`).
- The schema is dumped to `var/orm-schema.php` by `bin/console orm:schema-dump` during the image build and loaded by `DbContext` via `Schema::fromFile()`. Without the file it is built from entity attributes at process start, so entity changes need a backend restart, not a cache flush. The dev override bind-mounts only `src/`, so mount an empty `./backend/var:/app/var` there too, otherwise the image's dumped schema shadows entity edits. Don't leave a locally dumped `backend/var/` around.
- Long-running workers clear `getIdentityMap()` after each request/message. After migrating on an open connection, call `getDatabase()->clearStatementCache()`.

## Adding a Repository

When adding a new `Model/Repository/*Repository.php` you MUST also register it in `src/App/ServiceProvider/OrmServiceProvider.php`:

1. Add `use` for the entity and repository classes.
2. Add the repository class to the `provides()` array.
3. Add `$this->addRepository($container, $orm, FooRepository::class, Foo::class);` in `register()`.

Skipping any of these makes the repository unresolvable through the container.

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

## Testing Patterns

- PHPUnit 13 with `#[CoversClass]`/`#[UsesClass]` attributes, `createStub()`/`createMock()`
- **Final class mocking rule:** PHPUnit cannot double `final` classes. If a `final` service needs to be mocked in tests, extract an interface (`*Interface`), make the class implement it, and register the interface binding in `ApplicationFactory`. Use the interface as the type-hint in consumers and test stubs.
- For `final` repository/infrastructure classes that only need to be "never called" in a test, use `(new ReflectionClass(Foo::class))->newInstanceWithoutConstructor()` — any accidental call throws an Error as an implicit assertion.
- For `readonly class` stubs, set uninitialized readonly properties via `ReflectionProperty::setValue($stub, $value)`.
- `assertEquals()` on `Decimal` compares by numeric value (`2.5` equals `2.50`) via `tests/Comparator/DecimalComparator.php`, registered in `tests/bootstrap.php`. Without it, the result depends on the ext-decimal version: 1.5.0 treats any two Decimals as equal, 1.5.3 compares string forms.
- ext-decimal is pinned to `1.5.3` in `backend/Dockerfile` and in the CI image in `.github/workflows/backend.yml`. Keep the two in sync.

## Code Quality

- PHPStan max level, cognitive complexity limits: 25 per function, 50 per class
- Strict typing (PHP 8.5+)
