# Changelog

All notable changes to fingather are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.17.0] - 2026-06-27
### Changed
- Upgraded the frontend to Angular 22 and refreshed Composer/pnpm dependencies and Docker images.
- Containers now expose readiness healthchecks and service startup is gated on dependencies being healthy.
### Fixed
- Corrected the Docker Compose dependency direction so the proxy starts after the services it routes to.

## [1.16.0] - 2026-06-23
### Changed
- MCP server is now served at `/mcp` instead of `/api/mcp`.
- Upgraded Docker images and Composer/pnpm dependencies.
### Fixed
- Most-used tickers query corrected for ORM 1.2.0.

## [1.15.3] - 2026-06-13
### Security
- Secret values (database, Stripe, encryption, JWT and external API keys, request authorization tokens) are now masked in error logs instead of being written in plaintext.

## [1.15.2] - 2026-06-07
### Changed
- XTB importer now supports both the new single-sheet "Cash Operations" export and the legacy four-sheet layout.
### Fixed
- Czech translation of "Upcoming Dividends" on the dividends page.

## [1.15.1] - 2026-05-16
### Changed
- Tax optimization summary cards now sit side-by-side on tablet and wider; empty buckets are no longer rendered.

## [1.15.0] - 2026-05-12
### Added
- Risk analysis on the history page: volatility, max drawdown, Sharpe ratio, beta-to-benchmark, and a correlation heatmap across holdings.
### Changed
- Import Mappings list uses the shared asset-display component.
- Upgraded Docker images.
- Frontend lint now fails on warnings.
### Fixed
- Donut chart tooltip values now include the percent symbol.

## [1.14.0] - 2026-05-11
### Added
- Tax optimization page with tax-loss harvesting suggestions and cost-basis (FIFO/LIFO/average) comparison; per-portfolio tax settings.
- Slovakia and Germany tax jurisdictions with annual allowances; Czech Republic CZK 100,000 gross-proceeds exemption backfilled.
- Reusable skeleton loading table for opened, closed, and watched asset list tabs.
### Changed
- Asset list switchers moved into the tab row.

## [1.13.0] - 2026-05-09
### Added
- Time-Weighted Return (TWR) and Money-Weighted Return (MWR / XIRR) on the dashboard, accessed via a switcher on the gain/loss card.
### Changed
- Asset detail page hides About, DCF, and Fundamentals cards when their data is unavailable.

## [1.12.0] - 2026-05-08
### Added
- DCF (discounted cash flow) valuation calculator with assumptions, history points, and per-ticker valuation view.
### Fixed
- Backend coding-style violations across DCF DTOs and tests.

## [1.11.0] - 2026-05-07
### Changed
- Price alert notification dispatch moved to async RabbitMQ handler.
### Fixed
- Frontend build on pnpm 11.

## [1.10.0] - 2026-05-03
### Added
- DCA plan proxy assets.
- AUTHORIZATION_TOKEN_KEY length validation.
### Changed
- Backend CI now runs inside production-like Docker image.
### Fixed
- Chart formatting.

## [1.9.0] - 2026-04-30
### Added
- Admin impersonation via secure JWT-based "switch to user".
- Monte Carlo P10/P50/P90 bands on DCA projections.
- Delete option for user warmup.
### Fixed
- XTB import dropping partial open/close trades.
- XTB sell prices reflecting cost basis instead of sale price.
- XTB dividend tax pairing dropping taxes when rows aren't adjacent.
- XTB ticker resolution now scoped by country to avoid same-symbol collisions.
- Transaction edit back button when opened from asset detail.

## [1.8.1] - 2026-04-29
### Added
- GitHub Actions CI for backend, frontend, and e2e.
### Changed
- Backend unit-test coverage raised from 27% to 33%.
### Fixed
- Playwright/CI tooling reliability (timeouts, build optimisation, port readiness, baseURL).

## [1.8.0] - 2026-04-23
### Added
- 7 new MCP tools for richer AI portfolio discussions.

## [1.7.0] - 2026-04-18
### Changed
- MCP server authentication migrated from API key to OAuth 2.1 + PKCE.
### Fixed
- Encrypted API key handling.

## [1.6.1] - 2026-04-15
### Fixed
- N+1 in asset listing endpoints (eager-loaded ticker relations).
- Trading212 API integration (reapplied after temporary revert).

## [1.6.0] - 2026-04-06
### Added
- API key encryption.
- MCP server (initial implementation).
- Adminer basic auth.
### Fixed
- Trading212 API.
- Security hardening (alert innerHTML, php config).

## [1.5.1] - 2026-04-03
### Fixed
- FIFO partial-lot realised gain overcounting.
- Average price now uses volume-weighted (VWAP) calculation.
- Dividend tax distribution across multiple dividends on the same date.
- Interannual per-annum using year period instead of total lifetime.
### Changed
- BenchmarkDataCalculator made readonly (instance-level cache removed).
- FIFO lot matching extracted into FifoLotMatcher.

## [1.5.0] - 2026-03-28
### Added
- Card loading skeleton.
### Fixed
- Portu import.
- Transactions ordering.
- Alert styles, button colours, translations.

## [1.4.0] - 2026-03-25
### Added
- Goal reachability indicator.

## [1.3.0] - 2026-03-21
### Added
- CalculatorUtils helper.
- Service providers layer (refactored to interfaces).
- Params builder.
### Changed
- File response refactored to stream.
### Fixed
- TwelveData rate-limit retry.
- Alert destroy, refresh-token observable.

## [1.2.1] - 2026-03-18
### Added
- Error interceptor.
- Localization tests.
### Fixed
- TaxReportCalculator performance.
- Environment variable validation.
- Password validation, translations.

## [1.2.0] - 2026-03-15
### Added
- Strategy rebalancing calculator.
- Password requirements.
- E2E test infrastructure (Playwright + makefile + onboarding test).
- Email translations.
### Fixed
- Email styling and translation issues.

## [1.1.0] - 2026-03-09
### Added
- Password reset flow.
- Broker support.
- Improved monthly emails.
### Changed
- localStorage refactored into StorageService.
- Forms refactored to FormGroup.
- HTTP interceptor refactored.

## [1.0.0] - 2026-03-03
Initial milestone — pre-existing baseline. No prior release notes.
