-- Migration: link contract intake submissions to an existing person record
-- (optional — public submitters may still free-type a name not in the list).

SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND COLUMN_NAME  = 'submitter_person_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contract_intake_submissions ADD COLUMN submitter_person_id INT DEFAULT NULL AFTER submitter_department',
    'SELECT "submitter_person_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND INDEX_NAME   = 'idx_intake_submitter_person';

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_intake_submitter_person ON contract_intake_submissions (submitter_person_id)',
    'SELECT "idx_intake_submitter_person already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND CONSTRAINT_NAME = 'fk_intake_submitter_person';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contract_intake_submissions ADD CONSTRAINT fk_intake_submitter_person FOREIGN KEY (submitter_person_id) REFERENCES people (person_id) ON DELETE SET NULL',
    'SELECT "fk_intake_submitter_person already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
