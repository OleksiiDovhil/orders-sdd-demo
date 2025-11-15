## ADDED Requirements

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

