# Frontend CLAUDE.md

Angular 21 standalone components with signal-based state.

## Commands

```bash
pnpm run start       # Dev server with SSL on localhost
pnpm run build-prod  # Production build
pnpm run lint        # ESLint + Stylelint
pnpm run lint-fix    # Auto-fix lint issues
pnpm run test        # Vitest
pnpm run test:watch  # Vitest watch mode
pnpm run test:coverage
```

## Structure (`/frontend/src/app`)

- **models/**: TypeScript interfaces matching backend DTOs
- **services/**: HTTP services for each domain entity
- Feature modules: assets, portfolios, groups, transactions, dividends, users, etc.

## Key Patterns

- Angular 21 standalone components
- Signal-based state: `signal()`, `computed()`
- `inject()` for DI — no constructor injection
- Services use `async` methods returning promises
- Models mirror backend DTOs
- i18n via @ngx-translate (`{{ 'key' | translate }}`)
- Date formatting: `| date: 'y-MM-dd'` for dates, `| date: 'HH:mm'` for time

## Testing (Vitest)

- Config: `vitest.config.ts` — uses `defineConfig` from `vite`
- Setup: `src/test-setup.ts` — imports `@angular/compiler`, zone.js, initialises TestBed
- Globals enabled: no imports needed for `describe`/`it`/`expect`/`vi`
- **Gotcha:** when a service method awaits a mocked dependency, add `await Promise.resolve()` before `httpMock.expectOne()` so the promise chain settles before flushing

## Code Quality

- ESLint + Stylelint with SCSS guidelines
- TypeScript strict mode
