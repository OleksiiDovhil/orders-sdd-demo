-- Migration: Add is_paid column to orders table

ALTER TABLE orders
ADD COLUMN IF NOT EXISTS is_paid BOOLEAN NOT NULL DEFAULT false;

CREATE INDEX IF NOT EXISTS idx_orders_is_paid ON orders(is_paid);

