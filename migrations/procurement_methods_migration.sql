-- Migration: procurement_methods
-- Adds an admin-manageable lookup table for Procurement Method (replaces the
-- hardcoded dropdown list in app/views/contracts/edit.php) with ID / Short Desc /
-- Long Desc fields. Only "short_desc" is shown in the dropdown; both short_desc
-- and long_desc are available as merge fields for template generation.
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < procurement_methods_migration.sql

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `procurement_methods` (
  `procurement_method_id` int NOT NULL AUTO_INCREMENT,
  `short_desc` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `long_desc` text COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`procurement_method_id`),
  UNIQUE KEY `short_desc` (`short_desc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed with the values that were previously hardcoded in edit.php.
INSERT IGNORE INTO `procurement_methods` (`short_desc`, `sort_order`) VALUES
  ('Competitive Bid (IFB)', 10),
  ('Request for Proposals (RFP)', 20),
  ('Sole Source / Single Source', 30),
  ('Emergency Purchase', 40),
  ('Cooperative / Piggyback Purchase', 50),
  ('Small / Informal Purchase (below threshold)', 60),
  ('Professional Services (QBS)', 70),
  ('Service (non QBS)', 80),
  ('Not Required', 90);

-- Add the FK column to contracts (existing `procurement_method` text column is
-- left in place and kept in sync automatically by the application on save).
DROP PROCEDURE IF EXISTS add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE add_column_if_missing(
    in_table VARCHAR(64),
    in_column VARCHAR(64),
    in_column_def TEXT,
    in_after_column VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
          FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = in_table
           AND COLUMN_NAME = in_column
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', in_table, '` ADD COLUMN `', in_column, '` ', in_column_def,
                           IF(in_after_column <> '', CONCAT(' AFTER `', in_after_column, '`'), ''));
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL add_column_if_missing('contracts', 'procurement_method_id', 'INT NULL DEFAULT NULL', 'procurement_method');

DROP PROCEDURE IF EXISTS add_column_if_missing;

-- Backfill: match existing free-text procurement_method values to the new
-- lookup rows by exact text match.
UPDATE contracts c
JOIN procurement_methods pm ON pm.short_desc = c.procurement_method
SET c.procurement_method_id = pm.procurement_method_id
WHERE c.procurement_method_id IS NULL;

-- Add the FK constraint (idempotent guard via a check on information_schema).
SET @fk_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'contracts'
      AND CONSTRAINT_NAME = 'fk_contracts_procurement_method'
);
SET @ddl_fk = IF(@fk_exists = 0,
    'ALTER TABLE `contracts` ADD CONSTRAINT `fk_contracts_procurement_method` FOREIGN KEY (`procurement_method_id`) REFERENCES `procurement_methods` (`procurement_method_id`) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt_fk FROM @ddl_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

COMMIT;
