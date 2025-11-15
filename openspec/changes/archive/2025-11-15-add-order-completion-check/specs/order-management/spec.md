## ADDED Requirements

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

