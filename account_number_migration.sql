-- Migration: add account_number to contracts
-- Safe to run multiple times (IF NOT EXISTS)
ALTER TABLE contracts
    ADD COLUMN IF NOT EXISTS account_number VARCHAR(20) DEFAULT NULL AFTER po_number;
