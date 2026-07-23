#!/usr/bin/env bash
set -euo pipefail

# Generate a new SQL migration scaffold in the repo root.
# Usage:
#   scripts/new_sql_migration.sh add_nextcloud_tokens
# Output:
#   add_nextcloud_tokens_migration.sql

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <migration_name>"
  echo "Example: $0 add_nextcloud_tokens"
  exit 1
fi

raw_name="$1"

# Normalize name: lowercase, non-alnum -> underscore, trim underscores.
name="$(echo "$raw_name" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/_/g; s/^_+//; s/_+$//')"
if [[ -z "$name" ]]; then
  echo "Error: migration name is empty after normalization."
  exit 1
fi

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
file_path="$repo_root/${name}_migration.sql"

if [[ -e "$file_path" ]]; then
  echo "Error: migration already exists: $file_path"
  exit 1
fi

cat > "$file_path" <<SQL
-- Migration: ${name}
-- Date: $(date '+%Y-%m-%d %H:%M:%S %Z')
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < ${name}_migration.sql

START TRANSACTION;

-- ---------------------------------------------------------------------------
-- 1) Optional helper procedure for idempotent column adds
-- ---------------------------------------------------------------------------
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
        SET @sql = CONCAT(
            'ALTER TABLE `', in_table, '` ',
            'ADD COLUMN `', in_column, '` ', in_column_def,
            CASE
              WHEN in_after_column IS NULL OR in_after_column = '' THEN ''
              ELSE CONCAT(' AFTER `', in_after_column, '`')
            END
        );

        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------------
-- 2) Write migration steps below
-- ---------------------------------------------------------------------------
-- Example:
-- CALL add_column_if_missing('contracts', 'example_field', 'VARCHAR(255) NULL DEFAULT NULL', 'name');


-- ---------------------------------------------------------------------------
-- 3) Cleanup
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS add_column_if_missing;

COMMIT;
SQL

echo "Created migration: $file_path"
echo "Next: edit it and add your SQL statements."
