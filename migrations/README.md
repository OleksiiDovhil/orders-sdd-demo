# Database Migrations

This directory contains SQL migration files for the order management system.

## Running Migrations

Execute the migrations in order using psql or your PostgreSQL client:

```bash
# From the project root
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/001_create_order_number_sequences.sql
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/002_create_orders_table.sql
docker-compose exec postgres psql -U symfony -d symfony -f /var/www/html/migrations/003_create_order_items_table.sql
```

Or using make:

```bash
make exec ARGS="psql \$DATABASE_URL -f migrations/001_create_order_number_sequences.sql"
make exec ARGS="psql \$DATABASE_URL -f migrations/002_create_orders_table.sql"
make exec ARGS="psql \$DATABASE_URL -f migrations/003_create_order_items_table.sql"
```

## Migration Files

1. `001_create_order_number_sequences.sql` - Creates the table for tracking monthly order number sequences
2. `002_create_orders_table.sql` - Creates the orders table
3. `003_create_order_items_table.sql` - Creates the order_items table

