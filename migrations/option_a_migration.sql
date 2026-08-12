-- Option A Migration: Link Development Agreements to Contracts
-- Run this AFTER development_agreements_migration.sql has been run.

-- 1. Add contract_id FK column to development_agreements (skip if already present)
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'development_agreements'
      AND COLUMN_NAME  = 'contract_id'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE development_agreements ADD COLUMN contract_id INT DEFAULT NULL AFTER dev_agreement_id',
    'SELECT 1 -- contract_id column already exists'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Add FK constraint (skip if already present)
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA    = DATABASE()
      AND TABLE_NAME      = 'development_agreements'
      AND CONSTRAINT_NAME = 'fk_devagr_contract'
);
SET @sql2 = IF(@fk_exists = 0,
    'ALTER TABLE development_agreements ADD CONSTRAINT fk_devagr_contract FOREIGN KEY (contract_id) REFERENCES contracts(contract_id) ON DELETE SET NULL',
    'SELECT 2 -- FK already exists'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- 3. Insert "Development Agreement" contract type (idempotent)
INSERT INTO contract_types (contract_type, is_active)
VALUES ('Development Agreement', 1)
ON DUPLICATE KEY UPDATE is_active = 1;
