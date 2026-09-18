-- Migration: let the assignee explain how they completed a task; captured at
-- completion time and included in the "task completed" email back to the assigner.

SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'tasks'
  AND COLUMN_NAME  = 'completion_notes';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE tasks ADD COLUMN completion_notes TEXT DEFAULT NULL AFTER completed_at',
    'SELECT "completion_notes already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
