# Design: Order Completion Check Endpoint

## Context
The order completion check endpoint needs to verify payment status for orders. The payment verification mechanism differs based on contractor type:
- **Legal entities (contractorType = 2)**: Payment status is checked via an external microservice
- **Individuals (contractorType = 1)**: Payment status is stored in the database as a boolean flag, updated by an external microservice

Currently, the system has no payment status tracking mechanism. We need to:
1. Add database field for individual payment status
2. Create an interface for payment status checking
3. Implement a mock microservice client for legal entities
4. Create the endpoint that orchestrates the check

## Goals / Non-Goals

### Goals
- Provide a single endpoint that returns payment status regardless of contractor type
- Abstract payment checking behind an interface to allow future real microservice integration
- Keep the endpoint simple and focused on payment status checking
- Support both contractor types with appropriate payment verification

### Non-Goals
- Implementing actual payment processing (handled by external systems)
- Implementing the real payment microservice (using mock for now)
- Payment status update mechanism (handled by external microservice)
- Payment retry or payment initiation (out of scope)

## Decisions

### Decision: Database Field for Individual Payment Status
**What**: Add `is_paid` BOOLEAN column to `orders` table, defaulting to `false`
**Why**: Individuals' payment status is determined by a database flag that gets updated by an external microservice. This is simpler than calling a microservice for every individual order check.
**Alternatives considered**:
- Call microservice for individuals too: Rejected - requirements specify database flag for individuals
- Separate payment_status table: Rejected - adds unnecessary complexity, payment is a property of the order

### Decision: Payment Status Service Interface
**What**: Create `PaymentStatusServiceInterface` in Domain layer with method `checkPaymentStatus(Order $order): bool`
**Why**: Follows Dependency Inversion Principle - domain depends on abstraction, infrastructure provides implementation. Allows easy swapping of mock implementation with real microservice later.
**Alternatives considered**:
- Direct microservice call in application layer: Rejected - violates DDD principles, harder to test
- Payment status as part of Order entity: Rejected - payment status is external state, not domain invariant

### Decision: Mock Microservice Client Implementation
**What**: Create `MockPaymentStatusService` that implements the interface and returns random true/false values
**Why**: Requirements specify "let it return true or false randomly for now". This allows development and testing without real microservice integration.
**Alternatives considered**:
- Always return false: Rejected - doesn't test both scenarios
- Always return true: Rejected - doesn't test both scenarios
- Configurable mock: Considered but rejected - random is simpler for initial implementation

### Decision: Query Handler Pattern (CQRS)
**What**: Use CQRS Query pattern with `CheckOrderCompletionQuery` and `CheckOrderCompletionHandler`
**Why**: Consistent with existing CQRS pattern in the codebase (CreateOrderCommand/Handler). Separates read operations from write operations.
**Alternatives considered**:
- Direct controller logic: Rejected - violates CQRS pattern, harder to test
- Service class: Considered but rejected - CQRS is already established pattern

### Decision: Repository Method for Finding Order
**What**: Add `findByUniqueOrderNumber(UniqueOrderNumber $uniqueOrderNumber): ?Order` to repository interface
**Why**: Need to retrieve order by unique order number to check its payment status. This is a read operation that belongs in the repository.
**Alternatives considered**:
- Find by ID: Rejected - endpoint receives unique order number, not ID
- Separate query repository: Considered but rejected - adds unnecessary complexity for single read method

## Risks / Trade-offs

### Risk: Mock Service Returns Inconsistent Results
**Mitigation**: Tests should verify both true and false scenarios. In production, replace with real microservice implementation.

### Risk: Database Field Not Updated by External Service
**Mitigation**: This is expected behavior - the external microservice is responsible for updating the field. Our endpoint only reads the status.

### Risk: Performance of Microservice Calls
**Mitigation**: For now, mock implementation is fast. When implementing real microservice, consider caching or async updates if performance becomes an issue.

## Migration Plan

1. Add database migration for `is_paid` column
2. Deploy code changes
3. External microservice will update `is_paid` field for individual orders
4. No data migration needed (defaults to false, which is correct for existing orders)

## Open Questions
- What should the endpoint return when order is not found? (Assumed: 404 Not Found)
- Should there be rate limiting on this endpoint? (Out of scope for now)
- What is the expected response format? (Assumed: JSON with payment status and appropriate message)

