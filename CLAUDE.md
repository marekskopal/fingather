# CLAUDE.md

FinGather is a portfolio tracking application with PHP backend (FrankenPHP), Angular frontend, and MariaDB database. Uses memcached and Redis for caching, RabbitMQ for async job processing.

## Services

- `/backend/` — PHP backend (see `backend/CLAUDE.md`)
- `/frontend/` — Angular frontend (see `frontend/CLAUDE.md`)
- `/ios/` — Swift/SwiftUI iOS app (see `ios/CLAUDE.md`)

## Docker

```bash
docker compose up -d --build              # Full stack
docker compose --profile dev up -d        # With DB admin tools (Adminer, Buggregator)
```

## Testing

Root `Makefile` orchestrates all test suites:

```bash
make test           # All tests: unit (parallel) + e2e
make test-unit      # Backend + frontend unit tests in parallel
make test-backend   # PHPUnit only (no docker needed — pure unit tests)
make test-frontend  # Vitest only
make test-e2e       # Starts docker test stack → Playwright → tears down
make test-env-up    # docker compose -f docker-compose.test.yml up -d --build --wait
make test-env-down  # docker compose -f docker-compose.test.yml down
```

## External APIs

- **TwelveData**: Stock/crypto data (required API key in `.env`)
- **OpenFIGI**: Financial instrument identification
- **Trading212**: Broker API integration

## API Contract

Backend `Decimal` values (php-decimal) are serialised as **JSON strings**, not numbers. iOS and frontend consumers must handle this accordingly.
