.PHONY: build up down exec logs clean test test-unit test-feature test-coverage test-verbose test-file test-filter

# Build Docker containers
build:
	docker-compose build

# Start all services
up:
	docker-compose up -d

# Stop all services
down:
	docker-compose down

# Execute command in PHP-FPM container
exec:
	docker-compose exec php $(ARGS)

# View container logs
logs:
	docker-compose logs -f

# Remove containers and volumes
clean:
	docker-compose down -v
	docker-compose rm -f

# Run all tests
test:
	docker-compose exec -T php vendor/bin/phpunit tests/

# Run unit tests only
test-unit:
	docker-compose exec -T php vendor/bin/phpunit tests/Unit

# Run feature tests only
test-feature:
	docker-compose exec -T php vendor/bin/phpunit tests/Feature

# Run tests with coverage report
test-coverage:
	docker-compose exec -T php vendor/bin/phpunit --coverage-html coverage/ tests/

# Run tests with debug output
test-verbose:
	docker-compose exec -T php vendor/bin/phpunit tests/ --debug

# Run specific test file
# Usage: make test-file FILE=tests/Feature/Order/CreateOrderTest.php
test-file:
	@if [ -z "$(FILE)" ]; then \
		echo "Error: FILE parameter is required"; \
		echo "Usage: make test-file FILE=tests/Feature/Order/CreateOrderTest.php"; \
		exit 1; \
	fi
	docker-compose exec -T php vendor/bin/phpunit $(FILE)

# Run tests with filter
# Usage: make test-filter FILTER=CreateOrderRequest
test-filter:
	@if [ -z "$(FILTER)" ]; then \
		echo "Error: FILTER parameter is required"; \
		echo "Usage: make test-filter FILTER=CreateOrderRequest"; \
		exit 1; \
	fi
	docker-compose exec -T php vendor/bin/phpunit tests/ --filter $(FILTER)

