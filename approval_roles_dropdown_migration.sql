-- ─────────────────────────────────────────────────────────────────────────────
-- Migration: Populate approval_rules "Required Approval" dropdown from roles table
--
-- 1. Adds an `approval_key` column to `roles` (the value stored in approval_rules.required_approval).
--    When set on a role, that role appears in the "Required Approval" dropdown.
-- 2. Seeds approval_key for the five existing approval roles.
-- 3. Widens approval_rules.required_approval from ENUM to VARCHAR so new
--    approval types can be added without a schema change.
--
-- Safe to run multiple times.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. Add approval_key column to roles (only if it doesn't already exist)
DROP PROCEDURE IF EXISTS _add_approval_key_col;
DELIMITER $$
CREATE PROCEDURE _add_approval_key_col()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'roles'
          AND COLUMN_NAME  = 'approval_key'
    ) THEN
        ALTER TABLE `roles`
            ADD COLUMN `approval_key` VARCHAR(50) DEFAULT NULL
            COMMENT 'When set, this role appears in the Approval Rules Required Approval dropdown. Value is stored in approval_rules.required_approval.';
    END IF;
END$$
DELIMITER ;
CALL _add_approval_key_col();
DROP PROCEDURE IF EXISTS _add_approval_key_col;

-- Unique index so two roles can't share the same approval_key (idempotent)
DROP PROCEDURE IF EXISTS _add_approval_key_idx;
DELIMITER $$
CREATE PROCEDURE _add_approval_key_idx()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'roles'
          AND INDEX_NAME   = 'uq_roles_approval_key'
    ) THEN
        CREATE UNIQUE INDEX `uq_roles_approval_key` ON `roles` (`approval_key`);
    END IF;
END$$
DELIMITER ;
CALL _add_approval_key_idx();
DROP PROCEDURE IF EXISTS _add_approval_key_idx;

-- 2. Seed approval_key for the five existing approval roles
UPDATE `roles` SET `approval_key` = 'manager'      WHERE `role_key` = 'TOWN_MANAGER'  AND (`approval_key` IS NULL OR `approval_key` = '');
UPDATE `roles` SET `approval_key` = 'purchasing'   WHERE `role_key` = 'PROCUREMENT'   AND (`approval_key` IS NULL OR `approval_key` = '');
UPDATE `roles` SET `approval_key` = 'legal'        WHERE `role_key` = 'LEGAL_ADMIN'   AND (`approval_key` IS NULL OR `approval_key` = '');
UPDATE `roles` SET `approval_key` = 'risk_manager' WHERE `role_key` = 'RISK_MANAGER'  AND (`approval_key` IS NULL OR `approval_key` = '');
UPDATE `roles` SET `approval_key` = 'council'      WHERE `role_key` = 'TOWN_COUNCIL'  AND (`approval_key` IS NULL OR `approval_key` = '');

-- 3. Widen approval_rules.required_approval from ENUM to VARCHAR(50)
--    The stored values ('manager', 'purchasing', etc.) do NOT change.
ALTER TABLE `approval_rules`
    MODIFY COLUMN `required_approval` VARCHAR(50) NOT NULL;
