# Order Creation API Implementation Summary

## Implementation Status

The Order Creation API has been implemented following DDD architecture and CQRS patterns.

## What Has Been Implemented

### ✅ Domain Layer
- **Value Objects**: OrderId, OrderNumber, UniqueOrderNumber, ContractorType
- **Entities**: Order (aggregate root), OrderItem
- **Repository Interface**: OrderRepositoryInterface
- **Exceptions**: InvalidOrderNumberException, InvalidContractorTypeException
- **Domain Service**: OrderNumberGenerator

### ✅ Application Layer
- **Command**: CreateOrderCommand
- **Handler**: CreateOrderHandler
- **DTOs**: CreateOrderItemDTO, CreateOrderResponseDTO
- **Service**: RedirectUrlGenerator

### ✅ Infrastructure Layer
- **Repository**: OrderRepository (PDO implementation)
- **PDO Factory**: PDOFactory for database connection
- **Database Migrations**: 
  - `001_create_order_number_sequences.sql`
  - `002_create_orders_table.sql`
  - `003_create_order_items_table.sql`

### ✅ Presentation Layer
- **Controller**: CreateOrderController (POST /api/orders)
- **Request DTOs**: CreateOrderRequest, CreateOrderItemRequest
- **Response DTO**: CreateOrderResponse

### ✅ Configuration
- **Services**: Configured in `config/services.yaml`
- **Environment Variables**: PAYMENT_AGGREGATOR_BASE_URL
- **PDO Connection**: Configured via PDOFactory

## Database Schema

### orders table
- `id` (SERIAL PRIMARY KEY)
- `order_number` (INTEGER) - Sequential number
- `unique_order_number` (VARCHAR(50) UNIQUE) - Formatted string (YYYY-MM-NNNNN)
- `sum` (INTEGER)
- `contractor_type` (INTEGER) - 1 = individual, 2 = legal entity
- `created_at` (TIMESTAMP)

### order_items table
- `id` (SERIAL PRIMARY KEY)
- `order_id` (INTEGER, FOREIGN KEY)
- `product_id` (INTEGER)
- `price` (INTEGER)
- `quantity` (INTEGER)

### order_number_sequences table
- `id` (SERIAL PRIMARY KEY)
- `year_month` (VARCHAR(7) UNIQUE) - Format: YYYY-MM
- `sequence_number` (INTEGER)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## API Endpoint

**POST /api/orders**

### Request Body
```json
{
  "sum": 1000,
  "contractorType": 1,
  "items": [
    {
      "productId": 1,
      "price": 1000,
      "quantity": 1
    }
  ]
}
```

### Response (201 Created)
```json
{
  "uniqueOrderNumber": "2025-01-1",
  "redirectUrl": "http://some-pay-agregator.com/pay/2025-01-1"
}
```

### Error Response (400 Bad Request)
```json
{
  "error": "Order sum (1000) does not match items total (2000)"
}
```

## Setup Instructions

### 1. Install Dependencies
```bash
docker-compose exec php composer install
```

### 2. Run Database Migrations
```bash
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/001_create_order_number_sequences.sql
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/002_create_orders_table.sql
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/003_create_order_items_table.sql
```

### 3. Configure Environment Variables
Add to `.env`:
```env
PAYMENT_AGGREGATOR_BASE_URL=http://some-pay-agregator.com
```

### 4. Test the Endpoint
```bash
curl -X POST http://localhost:8080/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "sum": 1000,
    "contractorType": 1,
    "items": [
      {
        "productId": 1,
        "price": 1000,
        "quantity": 1
      }
    ]
  }'
```

## Order Number Generation

- **orderNumber**: Sequential integer starting from 1 each month
- **uniqueOrderNumber**: Formatted as `{year}-{month}-{orderNumber}` (e.g., `2025-01-1`)

The sequence resets at the start of each new month and is thread-safe using database transactions with `SELECT FOR UPDATE`.

## Redirect URLs

- **Individual (contractorType = 1)**: `{baseUrl}/pay/{uniqueOrderNumber}`
- **Legal Entity (contractorType = 2)**: `{baseUrl}/orders/{uniqueOrderNumber}/bill`

## Testing

### Note on PHPUnit
PHPUnit is not yet installed. To add testing:

1. Install PHPUnit:
```bash
docker-compose exec php composer require --dev phpunit/phpunit
```

2. Create test files following the structure:
```
tests/
├── Unit/
│   └── Domain/Order/
│       ├── Entity/
│       ├── ValueObject/
│       └── Service/
├── Feature/
│   └── Order/
│       └── CreateOrderTest.php
└── Integration/
    └── Infrastructure/
        └── Persistence/
```

## Next Steps

1. Install and configure PHPUnit
2. Write unit tests for domain layer
3. Write feature tests for API endpoint
4. Add OpenAPI documentation (nelmio/api-doc-bundle)
5. Add validation using Symfony Validator component
6. Add error handling improvements

## Architecture Notes

- **DDD**: Clear separation between Domain, Application, Infrastructure, and Presentation layers
- **CQRS**: Commands for write operations, separate from queries
- **Repository Pattern**: Interface in Domain, implementation in Infrastructure
- **PDO**: Direct SQL access as required (no ORM)
- **Thread Safety**: Order number generation uses database transactions with row-level locking

