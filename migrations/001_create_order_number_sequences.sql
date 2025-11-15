-- Migration: Create order_number_sequences table for tracking monthly order numbers
-- This table tracks the sequential order number, deriving year_month from created_at

CREATE TABLE IF NOT EXISTS order_number_sequences (
    id SERIAL PRIMARY KEY,
    sequence_number INTEGER NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_order_number_sequences_created_at ON order_number_sequences(created_at);

