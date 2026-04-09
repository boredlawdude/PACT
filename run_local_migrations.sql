-- Combined local migrations
-- Run with: mysql -u root -p contract_manager < run_local_migrations.sql

DROP PROCEDURE IF EXISTS add_column_if_missing;
DELIMITER $$
CREATE PROCEDURE add_column_if_missing(
    tbl VARCHAR(64), col VARCHAR(64), col_def TEXT, after_col VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', col_def, ' AFTER `', after_col, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- 1. Add exhibit_label column to contract_documents
CALL add_column_if_missing('contract_documents', 'exhibit_label', 'VARCHAR(50) NULL DEFAULT NULL', 'doc_type');

-- 2. Add procurement compliance fields to contracts
CALL add_column_if_missing('contracts', 'procurement_method',  'VARCHAR(100) NULL DEFAULT NULL', 'documents_path');
CALL add_column_if_missing('contracts', 'bid_rfp_number',      'VARCHAR(100) NULL DEFAULT NULL', 'procurement_method');
CALL add_column_if_missing('contracts', 'bid_documents_path',  'VARCHAR(500) NULL DEFAULT NULL', 'bid_rfp_number');
CALL add_column_if_missing('contracts', 'procurement_notes',   'TEXT NULL DEFAULT NULL',         'bid_documents_path');

-- 3. Add date_approved columns to contracts
CALL add_column_if_missing('contracts', 'date_approved_by_procurement', 'DATE NULL DEFAULT NULL', 'procurement_notes');
CALL add_column_if_missing('contracts', 'date_approved_by_manager',     'DATE NULL DEFAULT NULL', 'date_approved_by_procurement');
CALL add_column_if_missing('contracts', 'date_approved_by_council',     'DATE NULL DEFAULT NULL', 'date_approved_by_manager');

DROP PROCEDURE IF EXISTS add_column_if_missing;

-- 5. Add compliance_info_link system setting
INSERT INTO system_settings (setting_key, setting_value, description)
VALUES (
    'compliance_info_link',
    '',
    'URL linked from the Procurement & Public Bidding Compliance heading on contract create/edit. Leave blank to show plain text.'
)
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- 6. Create bidding_compliance table
CREATE TABLE IF NOT EXISTS `bidding_compliance` (
  `compliance_id`        int NOT NULL AUTO_INCREMENT,
  `contract_id`          int NOT NULL,
  `event_date`           date NOT NULL,
  `event_type`           varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment`              text COLLATE utf8mb4_unicode_ci,
  `contract_document_id` int DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at`           timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`compliance_id`),
  KEY `idx_bc_contract_id` (`contract_id`),
  CONSTRAINT `fk_bc_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bc_document` FOREIGN KEY (`contract_document_id`) REFERENCES `contract_documents` (`contract_document_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bc_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
