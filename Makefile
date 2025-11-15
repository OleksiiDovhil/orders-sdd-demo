.PHONY: build up down exec logs clean test test-coverage test-coverage-percent test-coverage-save test-coverage-check test-coverage-analyze test-coverage-auto-fix test-verbose test-file test-filter phpstan phpstan-src deptrack phpcbf phpcbf-src phpcs phpcs-src

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


# Run tests with coverage report
# Note: Requires Xdebug to be installed (rebuild container with 'make build' after Dockerfile changes)
test-coverage:
	@echo "Checking for Xdebug..."
	@docker-compose exec -T php php -m | grep -q xdebug || \
		(echo "❌ Error: Xdebug is not installed. Please rebuild the container:" && \
		 echo "   make build" && \
		 echo "   make up" && \
		 exit 1)
	docker-compose exec -T php mkdir -p coverage
	docker-compose exec -T -e XDEBUG_MODE=coverage php vendor/bin/phpunit \
		--coverage-html coverage/ \
		--coverage-clover coverage/clover.xml \
		--coverage-text=coverage/coverage.txt \
		tests/

# Extract coverage percentage from coverage report
# Note: Requires coverage report to be generated first with 'make test-coverage'
test-coverage-percent:
	@docker-compose exec -T php php scripts/extract-coverage.php coverage/clover.xml

# Run tests with coverage and save percentage to coverage_percent file
test-coverage-save:
	$(MAKE) test-coverage
	@docker-compose exec -T php php scripts/extract-coverage.php coverage/clover.xml > coverage_percent 2>&1 || \
		(echo "Failed to extract coverage percentage" && exit 1)
	@echo "Coverage percentage saved to coverage_percent: $$(cat coverage_percent)%"

# Run tests with coverage and check if coverage meets threshold in coverage_percent file
test-coverage-check:
	$(MAKE) test-coverage
	@docker-compose exec -T php php scripts/check-coverage-threshold.php coverage/clover.xml coverage_percent

# Analyze coverage report and identify uncovered code (useful when coverage decreased)
# Note: Requires coverage report to be generated first with 'make test-coverage'
test-coverage-analyze:
	@if [ ! -f "coverage/clover.xml" ]; then \
		echo "Error: Coverage report not found. Running test-coverage first..."; \
		$(MAKE) test-coverage; \
	fi
	@docker-compose exec -T php php scripts/analyze-coverage-and-generate-tests.php coverage/clover.xml
	@echo ""
	@echo "📄 For detailed method-level analysis, see: coverage/coverage.txt"
	@echo "📄 For interactive HTML report, visit: http://localhost:8080/coverage/"

# Automated coverage fix workflow: runs coverage, checks threshold, and provides prioritized list
# Prioritizes recently modified files (from git diff) as HIGHEST PRIORITY
test-coverage-auto-fix:
	@docker-compose exec -T php php scripts/auto-fix-coverage.php --git-diff

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

# Run PHPStan static analysis (all code including tests)
phpstan:
	docker-compose exec -T php php -d memory_limit=512M vendor/bin/phpstan analyse

# Run PHPStan static analysis for src folder only
phpstan-src:
	docker-compose exec -T php php -d memory_limit=512M vendor/bin/phpstan analyse src/

# Run deptrack dependency analysis
deptrack:
	docker-compose exec -T php php -d error_reporting="E_ALL & ~E_DEPRECATED" vendor/bin/deptrac analyse --config-file=deptrac.yaml

# Run phpcbf (PHP Code Beautifier and Fixer) to automatically fix code style issues (all code including tests)
# Note: phpcbf cannot fix line length violations automatically - they must be fixed manually
phpcbf:
	docker-compose exec -T php vendor/bin/phpcbf --standard=phpcs.xml src/ tests/ || \
	(EXIT_CODE=$$?; \
	if [ $$EXIT_CODE -eq 1 ]; then \
		echo "❌ phpcbf found errors that could not be fixed automatically"; \
		exit 1; \
	elif [ $$EXIT_CODE -eq 2 ]; then \
		echo "⚠ phpcbf completed but some violations remain (e.g., line length)"; \
		echo "   Line length violations must be fixed manually"; \
		echo "   Run 'make phpcs' to see remaining violations"; \
		exit 1; \
	else \
		exit $$EXIT_CODE; \
	fi)

# Run phpcbf (PHP Code Beautifier and Fixer) for src folder only
# Note: phpcbf cannot fix line length violations automatically - they must be fixed manually
phpcbf-src:
	docker-compose exec -T php vendor/bin/phpcbf --standard=phpcs.xml src/ || \
	(EXIT_CODE=$$?; \
	if [ $$EXIT_CODE -eq 1 ]; then \
		echo "❌ phpcbf found errors that could not be fixed automatically"; \
		exit 1; \
	elif [ $$EXIT_CODE -eq 2 ]; then \
		echo "⚠ phpcbf completed but some violations remain (e.g., line length)"; \
		echo "   Line length violations must be fixed manually"; \
		echo "   Run 'make phpcs-src' to see remaining violations"; \
		exit 1; \
	else \
		exit $$EXIT_CODE; \
	fi)

# Run phpcs (PHP CodeSniffer checker) to validate code style (all code including tests)
# Exit code 2 means warnings (e.g., line length) - we treat this as failure to enforce standards
phpcs:
	docker-compose exec -T php vendor/bin/phpcs --standard=phpcs.xml src/ tests/ || \
	(EXIT_CODE=$$?; \
	if [ $$EXIT_CODE -eq 1 ] || [ $$EXIT_CODE -eq 2 ]; then \
		echo "❌ CodeSniffer found violations (errors or warnings)"; \
		echo "   Run 'make phpcbf' to auto-fix some issues"; \
		echo "   Note: Line length violations must be fixed manually"; \
		exit 1; \
	else \
		exit $$EXIT_CODE; \
	fi)

# Run phpcs (PHP CodeSniffer checker) for src folder only
# Exit code 2 means warnings (e.g., line length) - we treat this as failure to enforce standards
phpcs-src:
	docker-compose exec -T php vendor/bin/phpcs --standard=phpcs.xml src/ || \
	(EXIT_CODE=$$?; \
	if [ $$EXIT_CODE -eq 1 ] || [ $$EXIT_CODE -eq 2 ]; then \
		echo "❌ CodeSniffer found violations (errors or warnings)"; \
		echo "   Run 'make phpcbf-src' to auto-fix some issues"; \
		echo "   Note: Line length violations must be fixed manually"; \
		exit 1; \
	else \
		exit $$EXIT_CODE; \
	fi)

