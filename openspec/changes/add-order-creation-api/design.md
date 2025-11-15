## Context
The system needs to accept order creation requests via REST API, generate unique order numbers, persist order data, and redirect users to payment aggregators based on contractor type. This feature requires database persistence, follows DDD architecture, uses CQRS pattern, and must integrate with external payment services.

## Goals / Non-Goals

### Goals
- Provide REST API endpoint for order creation
- Generate two order number fields:
  - `orderNumber`: Sequential integer (e.g., `12345`)
  - `uniqueOrderNumber`: Formatted string `{year}-{month}-{orderNumber}` (e.g., `2020-09-12345`)
- Persist orders using PDO and Repository pattern
- Redirect users to appropriate payment aggregator URLs based on contractor type
- Follow DDD architecture with clear separation of concerns
- Provide OpenAPI documentation for the endpoint
- Ensure data consistency and validation

### Non-Goals
- Payment processing (handled by external aggregator)
- Order status management (future feature)
- Order modification or cancellation (future feature)
- Product catalog management (assumes products exist)
- Authentication/authorization (assumes handled by framework)

## Decisions

### Decision: Use PDO for Database Access
- **What**: Implement repository using PDO instead of ORM (Doctrine)
- **Why**:
  - Explicit requirement from user
  - Direct SQL control for performance
  - Simpler dependency (no ORM overhead)
  - Better alignment with repository pattern (explicit data access)
- **Alternatives considered**:
  - Doctrine ORM: Rejected due to explicit PDO requirement
  - Query Builder: Could be added later if needed

### Decision: Repository Pattern Implementation
- **What**: Repository interface in Domain layer, PDO implementation in Infrastructure layer
- **Why**:
  - Follows DDD principles (domain defines contracts)
  - Enables testability (mock repository in domain tests)
  - Clear separation of concerns
  - Aligns with project architecture
- **Alternatives considered**:
  - Active Record: Rejected as violates DDD principles
  - Data Mapper in Domain: Rejected as violates dependency rule

### Decision: Order Number Generation Strategy
- **What**: Generate two order number fields:
  - `orderNumber`: Sequential integer starting from 1 per month (e.g., `12345`)
  - `uniqueOrderNumber`: Formatted string `{year}-{month}-{orderNumber}` (e.g., `2020-09-12345`)
- **Why**:
  - Explicit requirement from user
  - Provides both numeric identifier and human-readable formatted string
  - Shorter numbers than UUIDs
  - Human-readable format for uniqueOrderNumber
- **Implementation approach**:
  - Track sequence in database table (e.g., `order_number_sequences` with year-month key)
  - Generate `orderNumber` as sequential integer
  - Generate `uniqueOrderNumber` by formatting `orderNumber` with current year-month
  - Use database transactions/locks for thread-safety
  - Reset sequence at month start
- **Alternatives considered**:
  - UUID: Rejected as doesn't match required format
  - Timestamp-based: Rejected as doesn't provide sequential numbering

### Decision: Redirect URL Configuration
- **What**: Make payment aggregator base URL configurable via environment variables
- **Why**:
  - Different URLs for dev/staging/production
  - Easy to change without code modification
  - Follows 12-factor app principles
- **URL patterns**:
  - Individual: `{baseUrl}/pay/{uniqueOrderNumber}`
  - Legal Entity: `{baseUrl}/orders/{uniqueOrderNumber}/bill`
- **Alternatives considered**:
  - Hardcoded URLs: Rejected as not flexible
  - Database configuration: Rejected as overkill for simple URLs

### Decision: CQRS Pattern for Order Creation
- **What**: Use Command (CreateOrderCommand) and Handler (CreateOrderHandler)
- **Why**:
  - Aligns with project architecture (CQRS pattern)
  - Separates write operations from queries
  - Enables future event sourcing if needed
  - Clear use case boundaries
- **Alternatives considered**:
  - Direct service call: Rejected as doesn't follow CQRS
  - Application service: Could work but CQRS is preferred

### Decision: OpenAPI Documentation Library
- **What**: Use `nelmio/api-doc-bundle` for OpenAPI/Swagger documentation
- **Why**:
  - Standard Symfony bundle for API documentation
  - Good integration with Symfony
  - Supports OpenAPI 3.0
  - Active maintenance
- **Alternatives considered**:
  - Manual OpenAPI YAML: Rejected as harder to maintain
  - Other bundles: Could evaluate, but nelmio is standard

### Decision: Order Aggregate Structure
- **What**: Order as aggregate root containing OrderItems
- **Why**:
  - Order and items form consistency boundary
  - Items don't exist without order
  - Single transaction for order + items
  - Aligns with DDD aggregate pattern
- **Alternatives considered**:
  - Separate aggregates: Rejected as items are part of order consistency

## Risks / Trade-offs

### Risk: Order Number Sequence Thread-Safety
- **Issue**: Concurrent requests might generate duplicate order numbers
- **Mitigation**: Use database transactions with row-level locking or sequence table with proper locking
- **Implementation**: Use `SELECT FOR UPDATE` or database sequences/auto-increment for `orderNumber`, then format to `uniqueOrderNumber`

### Risk: Month Rollover Handling
- **Issue**: Sequence must reset at month start
- **Mitigation**: Check current month when generating number, reset sequence if month changed
- **Implementation**: Compare stored month with current month in generation logic

### Risk: Payment Aggregator URL Changes
- **Issue**: External URLs might change
- **Mitigation**: Make URLs configurable via environment variables
- **Trade-off**: Requires deployment for URL changes (acceptable)

### Risk: Order Sum Validation
- **Issue**: Sum must match sum of items
- **Mitigation**: Validate in domain/application layer before persistence
- **Implementation**: Calculate items total and compare with provided sum

### Risk: Database Transaction Failure
- **Issue**: Partial order creation (order without items)
- **Mitigation**: Use database transactions, rollback on any failure
- **Implementation**: Wrap order + items creation in single transaction

### Trade-off: PDO vs ORM
- **Decision**: PDO for explicit control and requirement compliance
- **Trade-off**: More manual SQL, but better performance and control

## Migration Plan

### Steps
1. Create database migrations for orders and order_items tables
2. Implement domain layer (entities, value objects, repository interface)
3. Implement application layer (command, handler, services)
4. Implement infrastructure layer (PDO repository)
5. Implement presentation layer (controller, DTOs)
6. Add OpenAPI documentation
7. Write tests
8. Configure services and environment variables

### Rollback
- Remove database tables via migration rollback
- Remove code files
- No breaking changes to existing functionality

## Open Questions
- Should order numbers be globally unique or per contractor? (Assume globally unique)
- What happens if payment aggregator is unavailable? (Return redirect URL anyway, aggregator handles errors)
- Should we validate product existence? (Assume products exist, validation can be added later)
- What is the maximum order sum? (No limit specified, can add validation later)
- Should we support partial order creation? (No, all items must be provided)

