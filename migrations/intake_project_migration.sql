-- Migration: let a public contract intake submission be tied to a project
-- from project_manager_app's shared `projects` table, mirroring
-- contracts.project_id so reviewers can pre-fill it on import.

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_intake_submissions'
    AND COLUMN_NAME = 'project_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contract_intake_submissions` ADD COLUMN `project_id` int DEFAULT NULL AFTER `contract_type_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contract_intake_submissions'
    AND CONSTRAINT_NAME = 'fk_intake_project'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contract_intake_submissions`
     ADD KEY `idx_intake_project_id` (`project_id`),
     ADD CONSTRAINT `fk_intake_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
