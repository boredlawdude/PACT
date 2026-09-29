-- Migration: let a public contract intake submission indicate it's a
-- Change Order request against an existing contract, so reviewers can see
-- (and pre-fill) the link when importing it via contracts_create.

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_intake_submissions'
    AND COLUMN_NAME = 'parent_contract_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contract_intake_submissions` ADD COLUMN `parent_contract_id` int DEFAULT NULL AFTER `contract_type_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_intake_submissions'
    AND CONSTRAINT_NAME = 'fk_intake_parent_contract'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contract_intake_submissions`
     ADD KEY `idx_intake_parent_contract_id` (`parent_contract_id`),
     ADD CONSTRAINT `fk_intake_parent_contract` FOREIGN KEY (`parent_contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
