## 1. Database Changes
- [x] 1.1 Create migration to add `is_paid` BOOLEAN column to `orders` table (default: false, NOT NULL)

## 2. Domain Layer
- [x] 2.1 Add `findByUniqueOrderNumber(UniqueOrderNumber $uniqueOrderNumber): ?Order` method to `OrderRepositoryInterface`
- [x] 2.2 Create `PaymentStatusServiceInterface` in Domain layer with `checkPaymentStatus(Order $order): bool` method

## 3. Infrastructure Layer
- [x] 3.1 Implement `findByUniqueOrderNumber` method in `OrderRepository` (retrieve order and items from database)
- [x] 3.2 Create `MockPaymentStatusService` implementing `PaymentStatusServiceInterface` that returns random true/false
- [x] 3.3 Register `MockPaymentStatusService` in `config/services.yaml` as implementation of `PaymentStatusServiceInterface`

## 4. Application Layer
- [x] 4.1 Create `CheckOrderCompletionQuery` DTO with `uniqueOrderNumber` property
- [x] 4.2 Create `CheckOrderCompletionResponseDTO` with `isPaid` and `message` properties
- [x] 4.3 Create `CheckOrderCompletionHandler` that:
  - Finds order by unique order number
  - Checks payment status via `PaymentStatusServiceInterface`
  - Returns appropriate response DTO

## 5. Presentation Layer
- [x] 5.1 Create `CheckOrderCompletionRequest` DTO in `src/Presentation/Request/` with:
  - `uniqueOrderNumber` property (string) with validation (NotBlank, matches format pattern)
  - `createQuery()` method that converts request to `CheckOrderCompletionQuery`
- [x] 5.2 Update `RequestDeserializer` to handle path parameters for `CheckOrderCompletionRequest`:
  - Extract `uniqueOrderNumber` from route attributes (`$request->attributes->get('uniqueOrderNumber')`)
  - Create and validate `CheckOrderCompletionRequest` instance
- [x] 5.3 Create `CheckOrderCompletionController` with GET `/api/orders/{uniqueOrderNumber}/complete` endpoint:
  - Accept `CheckOrderCompletionRequest` as parameter (auto-injected via ValueResolver)
  - Call handler with query from request
  - Handle 404 when order not found (throw NotFoundHttpException or return 404)
  - Return JSON response with payment status and appropriate message
- [x] 5.4 Add route configuration (using `#[Route]` attribute in controller)

## 6. OpenAPI Documentation
- [x] 6.1 Add OpenAPI schema for `CheckOrderCompletionRequest` (path parameter: uniqueOrderNumber)
- [x] 6.2 Add OpenAPI schema for `CheckOrderCompletionResponse` (isPaid, message)
- [x] 6.3 Document GET `/api/orders/{uniqueOrderNumber}/complete` endpoint in controller
- [x] 6.4 Document error responses (404 Not Found, 500 Internal Server Error)

## 7. Testing
- [x] 7.1 Unit test: `CheckOrderCompletionRequest::createQuery()` - verify correct query creation
- [x] 7.2 Unit test: `CheckOrderCompletionRequest` validation - test NotBlank constraint
- [x] 7.3 Unit test: `CheckOrderCompletionRequest` validation - test format pattern validation
- [x] 7.4 Unit test: `RequestDeserializer` - verify path parameter extraction for `CheckOrderCompletionRequest`
- [x] 7.5 Unit test: `OrderRepository::findByUniqueOrderNumber` - find existing order
- [x] 7.6 Unit test: `OrderRepository::findByUniqueOrderNumber` - return null for non-existent order
- [x] 7.7 Unit test: `MockPaymentStatusService::checkPaymentStatus` - verify random return values
- [x] 7.8 Unit test: `CheckOrderCompletionHandler` - handle paid order (individual)
- [x] 7.9 Unit test: `CheckOrderCompletionHandler` - handle unpaid order (individual)
- [x] 7.10 Unit test: `CheckOrderCompletionHandler` - handle paid order (legal entity)
- [x] 7.11 Unit test: `CheckOrderCompletionHandler` - handle unpaid order (legal entity)
- [x] 7.12 Unit test: `CheckOrderCompletionHandler` - handle order not found
- [x] 7.13 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 200 with isPaid=true for paid individual order
- [x] 7.14 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 200 with isPaid=false for unpaid individual order
- [x] 7.15 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 200 with isPaid=true for paid legal entity order (mock returns true)
- [x] 7.16 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 200 with isPaid=false for unpaid legal entity order (mock returns false)
- [x] 7.17 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 404 when order not found
- [x] 7.18 Feature test: GET `/api/orders/{uniqueOrderNumber}/complete` - return 400 when uniqueOrderNumber format is invalid
- [x] 7.19 Feature test: Verify response structure matches expected format (isPaid, message)

## 8. Validation
- [x] 8.1 Run all tests: `make test` or `docker-compose exec php bin/phpunit`
- [x] 8.2 Verify all tests pass
- [x] 8.3 Test endpoint manually with curl for both contractor types (verified via feature tests)
- [x] 8.4 Verify OpenAPI documentation is generated correctly

