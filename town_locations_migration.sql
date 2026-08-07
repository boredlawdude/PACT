-- Migration: town_locations
-- Adds a `town_locations` table (reusable list of the town's own addresses —
-- town hall, public works garage, water plant, parks & rec, etc. — that a
-- contract's deliverables/work can be FOB'd or performed at), links it to
-- `contracts` via `town_location_id`, and adds Town Manager / Town Clerk /
-- Town Attorney / Finance Director person-picker fields to
-- `organization_settings`.
--
-- Safe to re-run: all steps check for existing objects first.
-- Run with:
--   mysql -u root -p contract_manager < town_locations_migration.sql

-- ---------------------------------------------------------------------------
-- 1) town_locations table
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `town_locations` (
  `location_id`     int          NOT NULL AUTO_INCREMENT,
  `location_name`   varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line1`   varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2`   varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city`            varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_region`    varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code`     varchar(20)  COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active`       tinyint(1)   NOT NULL DEFAULT 1,
  `created_at`      timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2) Link contracts -> town_locations (additive, nullable)
-- ---------------------------------------------------------------------------
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND COLUMN_NAME = 'town_location_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contracts` ADD COLUMN `town_location_id` int DEFAULT NULL AFTER `owner_primary_contact_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND CONSTRAINT_NAME = 'fk_contracts_town_location'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contracts`
     ADD KEY `idx_contracts_town_location_id` (`town_location_id`),
     ADD CONSTRAINT `fk_contracts_town_location` FOREIGN KEY (`town_location_id`) REFERENCES `town_locations` (`location_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- 3) organization_settings -> people (Town Manager / Clerk / Attorney / Finance Director)
-- ---------------------------------------------------------------------------
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND COLUMN_NAME = 'town_manager_person_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `organization_settings` ADD COLUMN `town_manager_person_id` int DEFAULT NULL AFTER `mayor_or_exec_name`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND COLUMN_NAME = 'town_clerk_person_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `organization_settings` ADD COLUMN `town_clerk_person_id` int DEFAULT NULL AFTER `town_manager_person_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND COLUMN_NAME = 'town_attorney_person_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `organization_settings` ADD COLUMN `town_attorney_person_id` int DEFAULT NULL AFTER `town_clerk_person_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND COLUMN_NAME = 'finance_director_person_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `organization_settings` ADD COLUMN `finance_director_person_id` int DEFAULT NULL AFTER `town_attorney_person_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- FKs for the four new person-picker columns (each added only if missing).
SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND CONSTRAINT_NAME = 'fk_org_town_manager'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `organization_settings`
     ADD CONSTRAINT `fk_org_town_manager` FOREIGN KEY (`town_manager_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND CONSTRAINT_NAME = 'fk_org_town_clerk'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `organization_settings`
     ADD CONSTRAINT `fk_org_town_clerk` FOREIGN KEY (`town_clerk_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND CONSTRAINT_NAME = 'fk_org_town_attorney'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `organization_settings`
     ADD CONSTRAINT `fk_org_town_attorney` FOREIGN KEY (`town_attorney_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'organization_settings'
    AND CONSTRAINT_NAME = 'fk_org_finance_director'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `organization_settings`
     ADD CONSTRAINT `fk_org_finance_director` FOREIGN KEY (`finance_director_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
