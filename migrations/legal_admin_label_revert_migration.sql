-- Migration: revert LEGAL_ADMIN role display label back to "Legal Admin"
-- Supersedes legal_admin_town_attorney_label_migration.sql (removed) — renaming
-- LEGAL_ADMIN's role_name to "Town Attorney" caused two different roles/approval
-- types (LEGAL_ADMIN approval_key='legal' and the pre-existing TOWN_ATTORNEY
-- approval_key='town_attorney') to both display as "Town Attorney", which was
-- confusing. Legal Admin remains a fully separate, functional approval type
-- (Approval Rules, Contracts detail Approvals panel) — it is simply no longer
-- shown on the Dashboard "My Pending Approvals" widget (see
-- DashboardController::index()'s $dashboardExcludedApprovalKeys). The Dashboard
-- widget now surfaces ONLY the Town Attorney approval type (role_key
-- TOWN_ATTORNEY, approval_key 'town_attorney'), counting contracts with no
-- stamp in contract_approval_stamps for that key.
--
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < legal_admin_label_revert_migration.sql

UPDATE `roles` SET `role_name` = 'Legal Admin' WHERE `role_key` = 'LEGAL_ADMIN';
