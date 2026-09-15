-- Migration: add submitted_by_person_id to contracts
-- Records who actually submitted/created the contract request, distinct from
-- owner_primary_contact_id (the responsible person / contract administrator),
-- so a submitter can filter down to "just what I submitted".

SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contracts'
  AND COLUMN_NAME  = 'submitted_by_person_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE contracts ADD COLUMN submitted_by_person_id INT DEFAULT NULL AFTER owner_primary_contact_id',
    'SELECT "submitted_by_person_id already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @idx_exists
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contracts'
  AND INDEX_NAME   = 'idx_contracts_submitted_by';

SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_contracts_submitted_by ON contracts (submitted_by_person_id)',
    'SELECT "idx_contracts_submitted_by already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @fk_exists
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = @dbname
  AND TABLE_NAME   = 'contracts'
  AND CONSTRAINT_NAME = 'fk_contracts_submitted_by';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE contracts ADD CONSTRAINT fk_contracts_submitted_by FOREIGN KEY (submitted_by_person_id) REFERENCES people (person_id) ON DELETE SET NULL',
    'SELECT "fk_contracts_submitted_by already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
