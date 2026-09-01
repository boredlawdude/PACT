-- Migration: rename LEGAL_ADMIN role display label to "Town Attorney"
-- The Dashboard's "My Pending Approvals" widget and the Contracts show-page
-- Approvals panel both display roles.role_name for whichever approval types
-- are configured. This role (role_key = LEGAL_ADMIN, approval_key = 'legal',
-- backed by contracts.legal_approval_date) was previously labeled
-- "Legal Admin", showing as "Legal Admin Approval". Renamed to "Town Attorney"
-- per user request so it displays as "Town Attorney Approval".
--
-- NOTE: there is a separate, pre-existing role_key = TOWN_ATTORNEY
-- (approval_key = 'town_attorney') used for manual per-contract approval
-- overrides — this migration does NOT touch that row. After this change two
-- roles share the display name "Town Attorney" (LEGAL_ADMIN and TOWN_ATTORNEY)
-- but keep distinct role_key/approval_key values, so existing approval_rules
-- and stamps are unaffected.
--
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < legal_admin_town_attorney_label_migration.sql

UPDATE `roles` SET `role_name` = 'Town Attorney' WHERE `role_key` = 'LEGAL_ADMIN';
