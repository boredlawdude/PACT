-- One-time data backfill: Town Attorney approval stamps for pre-7/1/2026 contracts
--
-- Requested: for every contract submitted (created_at) before 2026-07-01 that
-- does NOT already have a Town Attorney approval stamp, insert one dated
-- 2026-09-01 (a retroactive "already approved" backfill so these older
-- contracts don't show up as pending on the new Dashboard "Town Attorney
-- Approval" widget). Safe to re-run — only inserts rows that don't already
-- exist (NOT EXISTS guard + the table's UNIQUE (contract_id, approval_key)
-- constraint as a second line of defense).
--
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < town_attorney_backfill_migration.sql

INSERT INTO `contract_approval_stamps` (`contract_id`, `approval_key`, `stamp_date`, `stamped_by_person_id`, `created_at`)
SELECT c.`contract_id`, 'town_attorney', '2026-09-01', NULL, NOW()
FROM `contracts` c
WHERE c.`created_at` < '2026-07-01'
  AND NOT EXISTS (
      SELECT 1 FROM `contract_approval_stamps` s
      WHERE s.`contract_id` = c.`contract_id` AND s.`approval_key` = 'town_attorney'
  );
