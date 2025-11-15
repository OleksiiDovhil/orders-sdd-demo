## 1. Domain Layer Implementation
- [x] 1.1 Create Order aggregate root entity with properties (id, orderNumber, uniqueOrderNumber, sum, contractorType, createdAt, items)
- [x] 1.2 Create OrderId value object
- [x] 1.3 Create OrderNumber value object (for sequential integer)
- [x] 1.3a Create UniqueOrderNumber value object (for formatted string)
- [x] 1.4 Create ContractorType value object/enum (individual=1, legalEntity=2)
- [x] 1.5 Create OrderItem entity (productId, price, quantity)
- [x] 1.6 Create OrderRepositoryInterface in Domain layer
- [x] 1.7 Add domain exceptions (InvalidOrderNumberException, InvalidContractorTypeException, etc.)

## 2. Application Layer Implementation
- [x] 2.1 Create CreateOrderCommand DTO with validation
- [x] 2.2 Create CreateOrderHandler implementing command handler pattern
- [x] 2.3 Create OrderNumberGenerator service (domain service)
- [x] 2.4 Create CreateOrderResponse DTO
- [x] 2.5 Implement order creation use case logic
- [x] 2.6 Add validation for order sum matches items total (REMOVED - sum is independent money field)

## 3. Infrastructure Layer Implementation
- [x] 3.1 Create database migration for `orders` table (id, order_number, unique_order_number, sum, contractor_type, created_at)
- [x] 3.2 Create database migration for `order_items` table (id, order_id, product_id, price, quantity)
- [x] 3.3 Implement OrderRepository using PDO
- [x] 3.4 Implement order number sequence generation (track sequential number per month, using created_at)
- [x] 3.5 Add database indexes for order_number, unique_order_number, and created_at
- [x] 3.6 Handle database transactions for order creation

## 4. Presentation Layer Implementation
- [x] 4.1 Create CreateOrderController with POST /api/orders route
- [x] 4.2 Create CreateOrderRequest DTO with validation rules
- [x] 4.3 Create CreateOrderResponse DTO
- [x] 4.4 Implement request validation and error handling
- [x] 4.5 Implement redirect URL generation based on contractorType (moved to Domain layer)
- [x] 4.6 Return appropriate HTTP status codes (201 Created, 400 Bad Request)

## 5. Order Number Generation
- [x] 5.1 Implement OrderNumberGenerator service
- [x] 5.2 Create database table/sequence for tracking monthly order numbers (using created_at, not separate year_month field)
- [x] 5.3 Implement logic to generate `orderNumber` (sequential integer)
- [x] 5.4 Implement logic to generate `uniqueOrderNumber` (formatted string: {year}-{month}-{orderNumber})
- [x] 5.5 Handle month rollover (reset sequence at month start, derived from created_at)
- [x] 5.6 Ensure thread-safety for concurrent order creation

## 6. Redirect Logic
- [x] 6.1 Create RedirectUrlGenerator service (moved to Domain layer)
- [x] 6.2 Implement redirect for individuals: http://some-pay-agregator.com/pay/{uniqueOrderNumber}
- [x] 6.3 Implement redirect for legal entities: http://some-pay-agregator.com/orders/{uniqueOrderNumber}/bill
- [x] 6.4 Make payment aggregator base URL configurable via environment variables
- [x] 6.5 Include redirect URL in API response

## 7. OpenAPI Documentation
- [ ] 7.1 Install OpenAPI/Swagger bundle (e.g., nelmio/api-doc-bundle)
- [ ] 7.2 Configure OpenAPI documentation
- [ ] 7.3 Add OpenAPI annotations/attributes to CreateOrderController
- [ ] 7.4 Document request schema (sum, contractorType, items array)
- [ ] 7.5 Document response schema (uniqueOrderNumber, redirectUrl, etc.)
- [ ] 7.6 Document error responses (400, 500, etc.)

## 8. Testing
- [ ] 8.1 Write unit tests for Order entity and value objects
- [ ] 8.2 Write unit tests for OrderNumberGenerator
- [ ] 8.3 Write unit tests for CreateOrderHandler
- [ ] 8.4 Write feature tests for POST /api/orders endpoint
- [ ] 8.5 Test orderNumber (sequential integer) and uniqueOrderNumber (formatted string) generation format and uniqueness
- [ ] 8.6 Test redirect URL generation for both contractor types
- [ ] 8.7 Test validation (invalid sum, missing items, invalid contractorType)
- [ ] 8.8 Test database persistence and transaction handling
- [ ] 8.9 Test concurrent order creation (thread-safety)
- [ ] 8.10 Test that orderNumber is saved in database `order_number` column
- [ ] 8.10a Test that uniqueOrderNumber is saved in database `unique_order_number` column
- [ ] 8.11 Run all unit tests and verify they pass
- [ ] 8.12 Run all feature tests and verify they pass
- [ ] 8.13 Verify test coverage meets project standards

## 9. Service Configuration
- [x] 9.1 Register OrderRepository interface to implementation in services.yaml
- [x] 9.2 Register OrderNumberGenerator service
- [x] 9.3 Register RedirectUrlGenerator service
- [x] 9.4 Configure payment aggregator base URL as parameter
- [x] 9.5 Ensure proper autowiring for all services

## 10. Documentation
- [x] 10.1 Update README with API endpoint documentation
- [x] 10.2 Document order number format
- [x] 10.3 Document redirect behavior
- [x] 10.4 Add example request/response to documentation

## 11. Test Execution and Validation
- [ ] 11.1 Run PHPUnit unit tests: `docker-compose exec php bin/phpunit tests/Unit`
- [ ] 11.2 Run PHPUnit feature tests: `docker-compose exec php bin/phpunit tests/Feature`
- [ ] 11.3 Run all tests: `docker-compose exec php bin/phpunit`
- [ ] 11.4 Verify all tests pass with exit code 0
- [ ] 11.5 Check test coverage if configured
- [ ] 11.6 Fix any failing tests before proceeding

## 12. Manual API Testing
- [ ] 12.1 Start application services: `make up` or `docker-compose up -d`
- [ ] 12.2 Test order creation for individual contractor via curl:
  ```bash
  curl -X POST http://localhost:8080/api/orders \
    -H "Content-Type: application/json" \
    -d '{"sum": 1000, "contractorType": 1, "items": [{"productId": 1, "price": 1000, "quantity": 1}]}'
  ```
- [ ] 12.3 Verify response contains uniqueOrderNumber and redirectUrl
- [ ] 12.4 Verify order is created in database with correct `order_number` and `unique_order_number`
- [ ] 12.5 Test order creation for legal entity contractor via curl:
  ```bash
  curl -X POST http://localhost:8080/api/orders \
    -H "Content-Type: application/json" \
    -d '{"sum": 2000, "contractorType": 2, "items": [{"productId": 2, "price": 2000, "quantity": 1}]}'
  ```
- [ ] 12.6 Verify response contains correct redirect URL format for legal entity
- [ ] 12.7 Verify uniqueOrderNumber format matches `{year}-{month}-{orderNumber}`
- [ ] 12.8 Test validation error handling with invalid request via curl
- [ ] 12.9 Verify database contains all created orders with proper `order_number` and `unique_order_number` values

