-- Migration: Create orders table

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    order_number INTEGER NOT NULL,
    unique_order_number VARCHAR(50) NOT NULL UNIQUE,
    sum INTEGER NOT NULL,
    contractor_type INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_orders_order_number ON orders(order_number);
CREATE INDEX IF NOT EXISTS idx_orders_unique_order_number ON orders(unique_order_number);
CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders(created_at);

