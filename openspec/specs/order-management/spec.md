# order-management Specification

## Purpose
TBD - created by archiving change add-order-creation-api. Update Purpose after archive.
## Requirements
### Requirement: Order Creation API Endpoint
The system SHALL provide a REST API endpoint that accepts order creation requests and creates new orders with proper validation, order number generation, and redirect URL generation.

#### Scenario: Create order with valid request
- **WHEN** a POST request is made to `/api/orders` with valid JSON body containing `sum`, `contractorType`, and `items` array
- **THEN** a new order is created in the database
- **AND** an `orderNumber` (sequential number, e.g., `12345`) is generated
- **AND** a `uniqueOrderNumber` (formatted string `{year}-{month}-{orderNumber}`, e.g., `2020-09-12345`) is generated
- **AND** the order and all order items are persisted
- **AND** HTTP status 201 (Created) is returned
- **AND** the response includes the uniqueOrderNumber and redirect URL

#### Scenario: Order number generation
- **WHEN** an order is created
- **THEN** an `orderNumber` (sequential integer) is generated starting from 1 for each month
- **AND** a `uniqueOrderNumber` (formatted string) is generated in format `{year}-{month}-{orderNumber}`
- **AND** year is 4 digits (e.g., 2020)
- **AND** month is 2 digits (e.g., 09)
- **AND** the orderNumber sequence resets at the start of each new month
- **EXAMPLE**: `orderNumber` = `12345`, `uniqueOrderNumber` = `2020-09-12345`

#### Scenario: Order number uniqueness
- **WHEN** multiple orders are created in the same month
- **THEN** each order receives a unique `orderNumber` (sequential integer)
- **AND** each order receives a unique `uniqueOrderNumber` (formatted string)
- **AND** no two orders have the same orderNumber or uniqueOrderNumber
- **AND** the orderNumber sequence increments for each new order

#### Scenario: Redirect URL for individual contractor
- **WHEN** an order is created with `contractorType` equal to 1 (individual)
- **THEN** the redirect URL is generated as `http://some-pay-agregator.com/pay/{uniqueOrderNumber}`
- **AND** the redirect URL uses the `uniqueOrderNumber` (formatted string)
- **AND** the redirect URL is included in the API response

#### Scenario: Redirect URL for legal entity contractor
- **WHEN** an order is created with `contractorType` equal to 2 (legal entity)
- **THEN** the redirect URL is generated as `http://some-pay-agregator.com/orders/{uniqueOrderNumber}/bill`
- **AND** the redirect URL uses the `uniqueOrderNumber` (formatted string)
- **AND** the redirect URL is included in the API response

#### Scenario: Request validation failure
- **WHEN** a POST request is made to `/api/orders` with invalid data (missing required fields, invalid types, negative values)
- **THEN** HTTP status 400 (Bad Request) is returned
- **AND** the response includes validation error messages
- **AND** no order is created in the database

#### Scenario: Order sum validation
- **WHEN** an order creation request is received
- **THEN** the system validates that the provided `sum` matches the sum of all item prices multiplied by quantities
- **AND** if the sums do not match, HTTP status 400 (Bad Request) is returned
- **AND** no order is created

### Requirement: Order Data Persistence
The system SHALL persist order data using PDO and the Repository pattern, storing orders and order items in a relational database.

#### Scenario: Order persistence
- **WHEN** an order is successfully created
- **THEN** the order is persisted in the `orders` table with fields: id, order_number, unique_order_number, sum, contractor_type, created_at
- **AND** the `orderNumber` (sequential integer, e.g., `12345`) is saved in the `order_number` column
- **AND** the `uniqueOrderNumber` (formatted string, e.g., `2020-09-12345`) is saved in the `unique_order_number` column
- **AND** all order items are persisted in the `order_items` table with fields: id, order_id, product_id, price, quantity
- **AND** the order and items are stored in a single database transaction
- **AND** if any part of the persistence fails, the entire transaction is rolled back
- **AND** both order numbers can be retrieved from the database

#### Scenario: Order number database persistence
- **WHEN** an order is created and persisted
- **THEN** the `orderNumber` (sequential integer) is stored in the `order_number` column of the `orders` table
- **AND** the `uniqueOrderNumber` (formatted string) is stored in the `unique_order_number` column of the `orders` table
- **AND** querying the database by `order_number` or `unique_order_number` returns the correct order
- **AND** the `unique_order_number` format in the database matches the generated format `{year}-{month}-{orderNumber}`

#### Scenario: Repository pattern implementation
- **WHEN** order data needs to be persisted
- **THEN** the system uses a repository interface defined in the Domain layer
- **AND** the repository implementation uses PDO for database access
- **AND** the repository implementation is located in the Infrastructure layer
- **AND** the repository is injected via dependency injection

### Requirement: OpenAPI Documentation
The system SHALL provide OpenAPI/Swagger documentation for the order creation endpoint.

#### Scenario: OpenAPI endpoint documentation
- **WHEN** the OpenAPI documentation is accessed
- **THEN** the POST `/api/orders` endpoint is documented
- **AND** the request schema is documented (sum, contractorType, items array with productId, price, quantity)
- **AND** the response schema is documented (uniqueOrderNumber, redirectUrl, etc.)
- **AND** error responses are documented (400, 500, etc.)
- **AND** example requests and responses are provided

### Requirement: Testing and Validation
The system SHALL have comprehensive unit and feature tests, and all tests SHALL pass before the feature is considered complete.

#### Scenario: Unit tests execution
- **WHEN** unit tests are executed
- **THEN** all unit tests for Order entity, value objects, OrderNumberGenerator, and CreateOrderHandler pass
- **AND** test execution completes with exit code 0
- **AND** all test assertions validate expected behavior

#### Scenario: Feature tests execution
- **WHEN** feature tests are executed
- **THEN** all feature tests for POST /api/orders endpoint pass
- **AND** tests verify order creation, validation, redirect URL generation, and database persistence
- **AND** test execution completes with exit code 0

#### Scenario: Manual API testing
- **WHEN** the API endpoint is tested manually via curl
- **THEN** a valid POST request to `/api/orders` successfully creates an order
- **AND** the response contains the uniqueOrderNumber and redirectUrl
- **AND** querying the database confirms the order exists with both `order_number` and `unique_order_number` saved
- **AND** the `unique_order_number` format matches `{year}-{month}-{orderNumber}`

### Requirement: Order Completion Check API Endpoint
The system SHALL provide a REST API endpoint that checks the payment status of an order and returns the appropriate result so users can see either a "thank you" page (if paid) or a reminder to complete payment (if not paid).

#### Scenario: Check completion for paid individual order
- **WHEN** a GET request is made to `/api/orders/{uniqueOrderNumber}/complete` for an order with `contractorType` equal to 1 (individual)
- **AND** the order's `is_paid` flag in the database is `true`
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response includes `isPaid: true`
- **AND** the response includes an appropriate message indicating payment was successful

#### Scenario: Check completion for unpaid individual order
- **WHEN** a GET request is made to `/api/orders/{uniqueOrderNumber}/complete` for an order with `contractorType` equal to 1 (individual)
- **AND** the order's `is_paid` flag in the database is `false`
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response includes `isPaid: false`
- **AND** the response includes an appropriate message reminding the user to complete payment

#### Scenario: Check completion for paid legal entity order
- **WHEN** a GET request is made to `/api/orders/{uniqueOrderNumber}/complete` for an order with `contractorType` equal to 2 (legal entity)
- **AND** the payment status microservice returns `true` (paid)
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response includes `isPaid: true`
- **AND** the response includes an appropriate message indicating payment was successful

#### Scenario: Check completion for unpaid legal entity order
- **WHEN** a GET request is made to `/api/orders/{uniqueOrderNumber}/complete` for an order with `contractorType` equal to 2 (legal entity)
- **AND** the payment status microservice returns `false` (not paid)
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response includes `isPaid: false`
- **AND** the response includes an appropriate message reminding the user to complete payment

#### Scenario: Order not found
- **WHEN** a GET request is made to `/api/orders/{uniqueOrderNumber}/complete` with a `uniqueOrderNumber` that does not exist
- **THEN** HTTP status 404 (Not Found) is returned
- **AND** the response includes an appropriate error message

### Requirement: Payment Status Database Field
The system SHALL store payment status for individual contractors in the database using an `is_paid` boolean field in the `orders` table.

#### Scenario: Individual order payment status storage
- **WHEN** an order is created with `contractorType` equal to 1 (individual)
- **THEN** the order is stored in the database with an `is_paid` column
- **AND** the `is_paid` column defaults to `false` (NOT NULL)
- **AND** the `is_paid` value can be updated by an external microservice
- **AND** the payment status can be retrieved when checking order completion

### Requirement: Payment Status Service for Legal Entities
The system SHALL check payment status for legal entity orders via a separate microservice interface.

#### Scenario: Payment status service interface
- **WHEN** payment status needs to be checked for a legal entity order
- **THEN** the system uses a `PaymentStatusServiceInterface` defined in the Domain layer
- **AND** the service implementation calls an external microservice to check payment status
- **AND** the service returns a boolean value indicating payment status
- **AND** the microservice implementation can be swapped without changing application logic

#### Scenario: Mock payment status service
- **WHEN** the payment status service is called for a legal entity order
- **AND** the mock implementation is used (for development/testing)
- **THEN** the service returns a random boolean value (true or false)
- **AND** the random value allows testing both paid and unpaid scenarios

### Requirement: Repository Method for Order Lookup
The system SHALL provide a repository method to find orders by unique order number.

#### Scenario: Find order by unique order number
- **WHEN** an order needs to be retrieved by its unique order number
- **THEN** the repository interface provides a `findByUniqueOrderNumber` method
- **AND** the method returns the Order entity if found, or null if not found
- **AND** the method retrieves both the order and its associated order items
- **AND** the repository implementation uses PDO for database access

### Requirement: Get Recent Orders API Endpoint
The system SHALL provide a REST API endpoint that returns information about the specified number of most recent orders.

#### Scenario: Get recent orders with valid limit
- **WHEN** a GET request is made to `/api/orders` with a valid `limit` query parameter
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response contains an array of orders
- **AND** the number of orders returned does not exceed the specified limit
- **AND** orders are returned in descending order by creation date (most recent first)
- **AND** each order in the response includes: `id` (unique order number), `sum` (total order amount), `contractorType` (contractor type), and `items` array
- **AND** each item in the `items` array includes: `productId`, `price`, and `quantity`

#### Scenario: Order response structure
- **WHEN** a GET request is made to `/api/orders` with a valid limit
- **THEN** each order in the response has the structure:
  - `id`: string (unique order number in format `YYYY-MM-NNNNN`)
  - `sum`: integer (total order amount)
  - `contractorType`: integer (1 = individual, 2 = legal entity)
  - `items`: array of objects, each containing:
    - `productId`: integer
    - `price`: integer (price per unit)
    - `quantity`: integer

#### Scenario: Limit parameter validation
- **WHEN** a GET request is made to `/api/orders` without a `limit` parameter
- **THEN** HTTP status 400 (Bad Request) is returned
- **AND** the response includes validation error messages

#### Scenario: Invalid limit parameter values
- **WHEN** a GET request is made to `/api/orders` with an invalid `limit` parameter (non-integer, negative, zero, or missing)
- **THEN** HTTP status 400 (Bad Request) is returned
- **AND** the response includes validation error messages
- **AND** no orders are returned

#### Scenario: Empty result when no orders exist
- **WHEN** a GET request is made to `/api/orders` with a valid limit
- **AND** no orders exist in the database
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response contains an empty array `[]`

#### Scenario: Limit exceeds available orders
- **WHEN** a GET request is made to `/api/orders` with a limit greater than the number of orders in the database
- **THEN** HTTP status 200 (OK) is returned
- **AND** the response contains all available orders (fewer than the requested limit)
- **AND** orders are still returned in descending order by creation date

#### Scenario: Repository method for recent orders
- **WHEN** recent orders need to be retrieved from the database
- **THEN** the repository interface provides a `findRecentOrders(int $limit): array` method
- **AND** the method returns an array of Order entities ordered by creation date (descending)
- **AND** the method limits results to the specified number
- **AND** the repository implementation uses PDO for database access
- **AND** the method efficiently loads orders and their items (using JOIN to avoid N+1 queries)

#### Scenario: OpenAPI documentation for recent orders endpoint
- **WHEN** the OpenAPI documentation is accessed
- **THEN** the GET `/api/orders` endpoint is documented
- **AND** the `limit` query parameter is documented (required, integer, minimum 1)
- **AND** the response schema is documented (array of order objects with id, sum, contractorType, items)
- **AND** error responses are documented (400, 500, etc.)
- **AND** example requests and responses are provided

