-- Migration: link contracts together as Change Orders, and let documents
-- be attached to a specific change_orders row.
--
-- 1. contracts.parent_contract_id — nullable self-referencing FK. When set,
--    this contract IS a full Change Order contract for another contract
--    (e.g. contract 1234 is a CO to contract 1). The parent contract's
--    Change Orders panel shows any contract with parent_contract_id = its id
--    alongside the "simple record" rows already in the change_orders table.
--
-- 2. contract_documents.change_order_id — nullable FK to change_orders.
--    Lets a document be uploaded specifically for a change_orders record
--    (a CO that is just a row, not a full linked contract). Since
--    contract_documents.contract_id is still set to the owning contract,
--    these documents automatically show up in that contract's main
--    Documents list as well.

-- ── contracts.parent_contract_id ─────────────────────────────────────────
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND COLUMN_NAME = 'parent_contract_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contracts` ADD COLUMN `parent_contract_id` int DEFAULT NULL AFTER `project_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND CONSTRAINT_NAME = 'fk_contracts_parent_contract'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contracts`
     ADD KEY `idx_contracts_parent_contract_id` (`parent_contract_id`),
     ADD CONSTRAINT `fk_contracts_parent_contract` FOREIGN KEY (`parent_contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── contract_documents.change_order_id ───────────────────────────────────
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_documents'
    AND COLUMN_NAME = 'change_order_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contract_documents` ADD COLUMN `change_order_id` int DEFAULT NULL AFTER `contract_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_documents'
    AND CONSTRAINT_NAME = 'fk_contract_documents_change_order'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contract_documents`
     ADD KEY `idx_contract_documents_change_order_id` (`change_order_id`),
     ADD CONSTRAINT `fk_contract_documents_change_order` FOREIGN KEY (`change_order_id`) REFERENCES `change_orders` (`change_order_id`) ON DELETE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
