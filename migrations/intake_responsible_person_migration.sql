-- Migration: let the intake submitter suggest a responsible person / contract
-- manager for the eventual contract (maps to contracts.owner_primary_contact_id
-- on import). Optional — Town staff can still change it during review.

SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND COLUMN_NAME  = 'responsible_person_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contract_intake_submissions ADD COLUMN responsible_person_id INT DEFAULT NULL AFTER submitter_person_id',
    'SELECT "responsible_person_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND INDEX_NAME   = 'idx_intake_responsible_person';

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_intake_responsible_person ON contract_intake_submissions (responsible_person_id)',
    'SELECT "idx_intake_responsible_person already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contract_intake_submissions'
  AND CONSTRAINT_NAME = 'fk_intake_responsible_person';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contract_intake_submissions ADD CONSTRAINT fk_intake_responsible_person FOREIGN KEY (responsible_person_id) REFERENCES people (person_id) ON DELETE SET NULL',
    'SELECT "fk_intake_responsible_person already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
