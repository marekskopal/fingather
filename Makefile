.PHONY: test test-unit test-backend test-frontend test-e2e test-env-up test-env-down

## Run all tests (unit + e2e)
test: test-unit test-e2e

## Run backend and frontend unit tests in parallel
test-unit:
	$(MAKE) -j2 test-backend test-frontend

## Run PHPUnit (no docker required — pure unit tests)
test-backend:
	cd backend && vendor/bin/phpunit

## Run Vitest
test-frontend:
	cd frontend && pnpm run test

## Start test docker stack, run Playwright, then tear down
test-e2e: test-env-up
	cd frontend && pnpm run e2e; \
	status=$$?; \
	$(MAKE) test-env-down; \
	exit $$status

## Start docker test services and wait until healthy
test-env-up:
	docker compose -f docker-compose.test.yml up -d --build --wait

## Stop docker test services
test-env-down:
	docker compose -f docker-compose.test.yml down
