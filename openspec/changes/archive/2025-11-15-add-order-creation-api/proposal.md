# Change: Add Order Creation API Endpoint

## Why
Enable the system to accept order creation requests via a REST API endpoint. This endpoint will create new orders with proper validation, generate unique order numbers, and redirect users to the appropriate payment aggregator based on contractor type. This is a foundational feature for order processing functionality.

## What Changes
- **ADDED**: REST API endpoint `POST /api/orders` for creating orders
- **ADDED**: Order domain model (Entity, Value Objects, Repository interface)
- **ADDED**: Order creation command and handler (CQRS pattern)
- **ADDED**: Database schema for orders and order items
- **ADDED**: PDO-based repository implementation for orders
- **ADDED**: Order number generation service that generates:
  - `orderNumber`: Sequential number (e.g., `12345`)
  - `uniqueOrderNumber`: Formatted string `{year}-{month}-{orderNumber}` (e.g., `2020-09-12345`)
- **ADDED**: Redirect logic based on contractor type (individual vs legal entity)
- **ADDED**: OpenAPI/Swagger documentation for the endpoint
- **ADDED**: Request/Response DTOs for order creation
- **ADDED**: Validation for order creation requests
- **ADDED**: Unit and Feature tests for all components
- **ADDED**: Test execution and validation
- **ADDED**: Manual API testing via curl

## Impact
- **Affected specs**: New capability `order-management`
- **Affected code**:
  - New files:
    - Domain layer: `src/Domain/Order/Entity/Order.php` (with `orderNumber` and `uniqueOrderNumber` properties), `src/Domain/Order/ValueObject/*`, `src/Domain/Order/Repository/OrderRepositoryInterface.php`
    - Application layer: `src/Application/Order/Command/CreateOrderCommand.php`, `src/Application/Order/Command/CreateOrderHandler.php`, `src/Application/Order/DTO/*`
    - Infrastructure layer: `src/Infrastructure/Persistence/OrderRepository.php`
    - Presentation layer: `src/Presentation/Controller/Order/CreateOrderController.php`, `src/Presentation/Request/CreateOrderRequest.php`, `src/Presentation/Response/CreateOrderResponse.php`
    - Database: Migration files for orders and order_items tables
  - Updated files:
    - `config/services.yaml` (service registration)
    - `config/routes.yaml` or controller attributes (routing)
- **Database**: New tables `orders` and `order_items` with two columns:
  - `order_number`: Sequential number (integer, e.g., `12345`)
  - `unique_order_number`: Formatted string `{year}-{month}-{orderNumber}` (e.g., `2020-09-12345`)
- **External dependencies**: May require OpenAPI/Swagger bundle (e.g., `nelmio/api-doc-bundle`)
- **Testing**: Comprehensive unit and feature tests with test execution validation
- **No breaking changes**: This is a new feature

