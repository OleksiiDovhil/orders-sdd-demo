## 1. Analyze Current Coverage Gaps
- [x] 1.1 Run `make test-coverage` to generate current coverage reports (XML, HTML, and text formats)
- [x] 1.2 Verify XML coverage report (`coverage/clover.xml`) is generated for line-by-line analysis
- [x] 1.3 Verify HTML coverage report is generated and available in `coverage/` directory (must remain in project)
- [x] 1.4 Parse XML coverage report (`coverage/clover.xml`) line by line to identify all uncovered lines
- [x] 1.5 Run `make test-coverage-analyze` to identify all uncovered code (uses XML internally)
- [x] 1.6 Review `coverage/coverage.txt` for detailed method-level coverage information
- [x] 1.7 Review HTML coverage report at `http://localhost:8080/coverage/` for visual line-by-line analysis
- [x] 1.8 Document all uncovered classes, methods, and lines that need test coverage based on XML analysis

## 2. Create OrderRepository Tests
- [x] 2.1 Create `tests/Unit/Infrastructure/Persistence/OrderRepositoryTest.php`
- [x] 2.2 Test `save()` method with new order (insert scenario)
- [x] 2.3 Test `save()` method with existing order (update scenario)
- [x] 2.4 Test `save()` method transaction rollback on exception
- [x] 2.5 Test `save()` method with order items insertion
- [x] 2.6 Test `getNextOrderNumber()` method with existing sequence
- [x] 2.7 Test `getNextOrderNumber()` method with new sequence creation
- [x] 2.8 Test `getNextOrderNumber()` method transaction rollback on exception
- [x] 2.9 Test `findByUniqueOrderNumber()` method with existing order
- [x] 2.10 Test `findByUniqueOrderNumber()` method with non-existent order (returns null)
- [x] 2.11 Test `findByUniqueOrderNumber()` method with invalid data structure (exception)
- [x] 2.12 Test `isPaid()` method with paid order
- [x] 2.13 Test `isPaid()` method with unpaid order
- [x] 2.14 Test `isPaid()` method with non-existent order
- [x] 2.15 Test `markAsPaid()` method

## 3. Create PDOFactory Tests
- [x] 3.1 Create `tests/Unit/Infrastructure/Persistence/PDOFactoryTest.php`
- [x] 3.2 Test `create()` method with PostgreSQL URL format
- [x] 3.3 Test `create()` method with MySQL URL format
- [x] 3.4 Test `create()` method with direct DSN format
- [x] 3.5 Test `create()` method sets PDO error mode to exception
- [x] 3.6 Test `create()` method sets default fetch mode to ASSOC
- [x] 3.7 Test `create()` method with URL containing username and password
- [x] 3.8 Test `create()` method with URL without username/password

## 4. Extend RequestDeserializer Tests
- [x] 4.1 Review existing `tests/Unit/Presentation/Request/RequestDeserializerTest.php`
- [x] 4.2 Add test for `deserializeAndValidate()` with `CreateOrderRequest` class
- [x] 4.3 Add test for `deserializeAndValidate()` with empty JSON content
- [x] 4.4 Add test for `deserializeAndValidate()` with invalid JSON format (exception)
- [x] 4.5 Add test for `deserializeAndValidate()` with JSON that doesn't decode to array (exception)
- [x] 4.6 Add test for `deserializeAndValidate()` with fallback serializer for other types
- [x] 4.7 Add test for `deserializeAndValidate()` with validation failures (ValidationFailedException)
- [x] 4.8 Add test for `deserializeCreateOrderRequest()` private method via public interface
- [x] 4.9 Add test for `deserializeCreateOrderRequest()` with invalid item data
- [x] 4.10 Add test for `deserializeCheckOrderCompletionRequest()` with non-string uniqueOrderNumber (exception)

## 5. Create RequestValueResolver Tests
- [x] 5.1 Create `tests/Unit/Presentation/Request/ValueResolver/RequestValueResolverTest.php`
- [x] 5.2 Test `resolve()` method with valid request DTO class
- [x] 5.3 Test `resolve()` method with null type (returns empty array)
- [x] 5.4 Test `resolve()` method with non-existent class (returns empty array)
- [x] 5.5 Test `resolve()` method with class not in `App\Presentation\Request\` namespace (returns empty array)
- [x] 5.6 Test `resolve()` method with base `Request` class (returns empty array)
- [x] 5.7 Test `resolve()` method exception handling (exception bubbles up)

## 6. Verify 100% Coverage
- [x] 6.1 Run `make test-coverage` to generate updated coverage reports (XML, HTML, and text formats)
- [x] 6.2 Verify XML coverage report (`coverage/clover.xml`) contains all source files
- [x] 6.3 Parse XML coverage report line by line to verify all lines in `src/` are covered (count="1" or higher)
- [x] 6.4 Verify all classes in `src/` have 100% method and line coverage using XML data
- [x] 6.5 Review HTML coverage report to visually confirm no uncovered code remains
- [x] 6.6 Update `coverage_percent` file to 100.00
- [x] 6.7 Run `make test-coverage-check` to verify coverage threshold is met

## 7. Test Execution and Validation
- [x] 7.1 Run PHPStan for src folder: `make phpstan-src` and fix any issues found
- [x] 7.2 Run CodeSniffer for src folder: `make phpcbf-src` to auto-fix issues, then `make phpcs-src` to verify
- [x] 7.3 Fix any remaining CodeSniffer violations in src folder that phpcbf could not auto-fix
- [x] 7.4 Run deptrac: `make deptrack` and fix any architectural violations found
- [x] 7.5 Run all tests: `make test` and verify all tests pass with exit code 0
- [x] 7.6 Fix any failing tests that may have been broken by code changes
- [x] 7.7 Run PHPStan globally (with tests folder): `make phpstan` and fix any issues found
- [x] 7.8 Run CodeSniffer globally (with tests folder): `make phpcbf` to auto-fix issues, then `make phpcs` to verify
- [x] 7.9 Fix any remaining CodeSniffer violations that phpcbf could not auto-fix
- [x] 7.10 Run automated coverage fix workflow: `make test-coverage-auto-fix` (or manually follow steps 7.11-7.14)
- [x] 7.11 Run tests with coverage: `make test-coverage` to generate coverage report
- [x] 7.12 Run coverage check: `make test-coverage-check` to compare current coverage with `coverage_percent` file
- [x] 7.13 If coverage check failed (coverage decreased): 
  - Run `make test-coverage-auto-fix` to get prioritized list of uncovered code
  - The tool will prioritize recently created/edited files (from git diff) as HIGHEST PRIORITY
  - Start adding tests for classes in priority order (highest priority first)
  - After each test addition, run `make test-coverage-check` to verify improvement
  - Continue until coverage check passes
- [x] 7.14 Verify test coverage is 100% after all test additions

