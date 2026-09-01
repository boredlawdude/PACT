-- Migration: document_categories
-- Adds an admin-manageable lookup table for the "Document Category" dropdown
-- shown on the Upload Document form (page=contract_document_create). Replaces
-- the hardcoded <option> list in app/views/contract_documents/create.php.
--
-- `is_system` = 1 marks the four original built-in categories whose
-- category_key values are relied on by app logic (ContractsController::storeDocument
-- special-cases 'exhibit' for the Exhibit Letter/Description fields, and
-- 'change_order' for auto-selecting the dropdown when uploading Change Order
-- support docs). System rows can be renamed/deactivated but never deleted.
-- Custom rows added via the admin UI have is_system = 0 and can be freely
-- edited/deleted.
--
-- Database: contract_manager
-- Run with:
--   mysql -u root -p contract_manager < document_categories_migration.sql

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `document_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_key` (`category_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed with the values that were previously hardcoded in
-- app/views/contract_documents/create.php.
INSERT IGNORE INTO `document_categories` (`category_key`, `label`, `is_system`, `sort_order`) VALUES
  ('revised_vendor',   'Revised by Vendor',                   1, 10),
  ('revised_internal', 'Revised Internally',                  1, 20),
  ('exhibit',          'Exhibit',                              1, 30),
  ('change_order',     'Change Order & Supporting Documents',  1, 40);

COMMIT;
