# Change: Add Order Completion Check API Endpoint

## Why
Users need to check whether their order has been paid after being redirected to the payment aggregator. The system must provide an endpoint that returns the payment status so users can see either a "thank you" page (if paid) or a reminder to complete payment (if not paid). Payment status checking differs based on contractor type: legal entities require a microservice call, while individuals use a database flag.

## What Changes
- Add GET endpoint `/api/orders/{uniqueOrderNumber}/complete` to check order payment status
- Add `is_paid` column to `orders` table for individual contractors
- Create payment status service interface and implementation for legal entity payment checks
- Create mock payment microservice client that returns random true/false values
- Add repository method to find order by unique order number
- Add repository method to check payment status for individual orders
- Return appropriate response based on payment status (paid vs not paid)
- Add comprehensive tests for all scenarios

## Impact
- Affected specs: `order-management` capability
- Affected code:
  - Database: New migration for `is_paid` column
  - Domain: New repository method interface
  - Infrastructure: Repository implementation, payment service client
  - Application: New query handler for order completion check
  - Presentation: New controller endpoint
  - Tests: Feature and unit tests for new endpoint

