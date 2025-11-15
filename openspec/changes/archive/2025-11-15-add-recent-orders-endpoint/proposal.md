# Change: Add Recent Orders Endpoint

## Why
The system needs to provide an API endpoint that returns the most recent orders, allowing clients to retrieve a specified number of recent orders. This is the third required endpoint from the Orders API specification (after Create Order and Complete Order).

## What Changes
- Add GET `/api/orders` endpoint that accepts a `limit` query parameter
- Add repository method `findRecentOrders(int $limit): array` to retrieve recent orders from database
- Add Query handler `GetRecentOrdersHandler` in Application layer
- Add Request DTO `GetRecentOrdersRequest` with validation for `limit` parameter
- Add Response DTOs for order list response
- Add Controller `GetRecentOrdersController` following existing patterns
- Add OpenAPI documentation for the new endpoint
- Add unit and feature tests for the new functionality

## Impact
- Affected specs: `order-management` (new requirement added)
- Affected code:
  - `src/Domain/Order/Repository/OrderRepositoryInterface.php` - new method
  - `src/Infrastructure/Persistence/OrderRepository.php` - implementation
  - `src/Application/Order/Query/` - new query handler and DTOs
  - `src/Presentation/Controller/Order/` - new controller
  - `src/Presentation/Request/` - new request DTO
  - `tests/` - new tests

