## 1. Implementation
- [x] 1.1 Add `findRecentOrders(int $limit): array` method to `OrderRepositoryInterface` in Domain layer
- [x] 1.2 Implement `findRecentOrders` method in `OrderRepository` using PDO, ordering by `created_at DESC` and limiting results
- [x] 1.3 Ensure the repository method loads order items in the same query (using JOIN) to avoid N+1 queries
- [x] 1.4 Create `GetRecentOrdersQuery` DTO in `src/Application/Order/Query/`
- [x] 1.5 Create `GetRecentOrdersHandler` in `src/Application/Order/Query/` that uses the repository method
- [x] 1.6 Create response DTOs for order list (e.g., `GetRecentOrdersResponseDTO`, `OrderListItemDTO`, `OrderItemDTO`)
- [x] 1.7 Create `GetRecentOrdersRequest` DTO in `src/Presentation/Request/` with validation for `limit` parameter (required, integer, minimum 1, maximum reasonable value)
- [x] 1.8 Create `GetRecentOrdersController` in `src/Presentation/Controller/Order/` following existing controller patterns
- [x] 1.9 Add OpenAPI documentation attributes to the controller
- [x] 1.10 Configure route for GET `/api/orders` endpoint
- [x] 1.11 Review database queries for optimization - ensure order items are loaded efficiently (single query with JOIN)
- [x] 1.12 Replace any string literals with Enums/constants if applicable (convert to strings only in presentation layer)

## 2. Testing
- [x] 2.1 Write unit tests for `GetRecentOrdersHandler` (test with different limits, empty results, etc.)
- [x] 2.2 Write unit tests for `GetRecentOrdersRequest` (test `createQuery()` method and validation)
- [x] 2.3 Write unit tests for repository `findRecentOrders` method
- [x] 2.4 Write feature tests for GET `/api/orders` endpoint:
  - [x] 2.4.1 Test successful retrieval with valid limit
  - [x] 2.4.2 Test with different limit values
  - [x] 2.4.3 Test ordering (most recent first)
  - [x] 2.4.4 Test response structure (id, sum, contractorType, items array)
  - [x] 2.4.5 Test validation errors (missing limit, invalid limit values, negative numbers, zero, non-integer)
  - [x] 2.4.6 Test empty result when no orders exist
  - [x] 2.4.7 Test that orders include all items correctly
- [x] 2.5 Review SQL query quality for `findRecentOrders` method:
  - [x] 2.5.1 Verify query is readable and well-formatted
  - [x] 2.5.2 Verify query uses appropriate indexes (created_at should be indexed)
  - [x] 2.5.3 Verify query performance is optimal (single query with JOIN, no N+1 problems)
  - [x] 2.5.4 Verify query uses LIMIT clause correctly
  - [x] 2.5.5 Verify query ordering is efficient (ORDER BY created_at DESC)
  - [x] 2.5.6 Test query execution time with realistic data volumes

## 3. Manual API Testing
- [ ] 3.1 Start application services: `make up` or `docker-compose up -d`
- [ ] 3.2 Test GET `/api/orders` endpoint with valid limit via curl:
  ```bash
  curl -X GET "http://localhost:8080/api/orders?limit=5" \
    -H "Accept: application/json"
  ```
- [ ] 3.3 Verify response contains array of orders with correct structure (id, sum, contractorType, items)
- [ ] 3.4 Verify orders are returned in descending order by creation date (most recent first)
- [ ] 3.5 Verify number of orders returned does not exceed the specified limit
- [ ] 3.6 Test with different limit values (1, 10, 100) and verify correct number of orders returned
- [ ] 3.7 Test endpoint without limit parameter and verify validation error (400 Bad Request):
  ```bash
  curl -X GET "http://localhost:8080/api/orders" \
    -H "Accept: application/json"
  ```
- [ ] 3.8 Test endpoint with invalid limit values (negative, zero, non-integer) and verify validation errors:
  ```bash
  curl -X GET "http://localhost:8080/api/orders?limit=-1" \
    -H "Accept: application/json"
  curl -X GET "http://localhost:8080/api/orders?limit=0" \
    -H "Accept: application/json"
  curl -X GET "http://localhost:8080/api/orders?limit=abc" \
    -H "Accept: application/json"
  ```
- [ ] 3.9 Test endpoint when no orders exist and verify empty array response `[]`
- [ ] 3.10 Verify database contains orders and items correctly after manual testing
- [ ] 3.11 Verify response JSON structure matches OpenAPI documentation

## 4. Test Execution and Validation
- [x] 4.1 Run PHPStan for src folder: `make phpstan-src` and fix any issues found
- [x] 4.2 Run CodeSniffer for src folder: `make phpcbf-src` to auto-fix issues, then `make phpcs-src` to verify
- [x] 4.3 Fix any remaining CodeSniffer violations in src folder that phpcbf could not auto-fix
- [x] 4.4 Run deptrac: `make deptrack` and fix any architectural violations found
- [x] 4.5 Run all tests: `make test` and verify all tests pass with exit code 0
- [x] 4.6 Fix any failing tests that may have been broken by code changes
- [x] 4.7 Run PHPStan globally (with tests folder): `make phpstan` and fix any issues found
- [x] 4.8 Run CodeSniffer globally (with tests folder): `make phpcbf` to auto-fix issues, then `make phpcs` to verify
- [x] 4.9 Fix any remaining CodeSniffer violations that phpcbf could not auto-fix
- [x] 4.10 Run tests with coverage: `make test-coverage` to generate coverage report (XML, HTML, and text formats)
- [x] 4.11 Analyze coverage report coverage.txt (then clover.xml if not 100% for all classes) to identify all uncovered lines in `src/` directory
- [x] 4.12 Add tests to cover all uncovered lines (only modify test files in `tests/` directory, not `src/`)
- [x] 4.13 If unreachable dead code is found that prevents 100% coverage, remove it from `src/` (this is the only exception to modifying `src/`)
- [x] 4.14 Run `make test-coverage` again and verify coverage is 100% (all classes, methods, and lines covered)
- [x] 4.15 Run all tests again: `make test` to ensure new tests don't break existing functionality

