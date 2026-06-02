-- ─────────────────────────────────────────────────────────────────────────────
-- Migration: contract_approval_stamps table
--
-- Stores approval stamp dates for approval types that do NOT have a dedicated
-- date column in the contracts table (i.e. anything beyond the original five:
-- manager, purchasing, legal, risk_manager, council).
--
-- The legacy five continue to use their dedicated contracts columns for backward
-- compatibility. All others use this table.
--
-- Safe to run multiple times.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `contract_approval_stamps` (
    `stamp_id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_id`         INT             NOT NULL,
    `approval_key`        VARCHAR(50)     NOT NULL,
    `stamp_date`          DATE            NOT NULL,
    `stamped_by_person_id` INT            DEFAULT NULL,
    `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`stamp_id`),
    UNIQUE KEY `uq_contract_approval` (`contract_id`, `approval_key`),
    KEY `idx_cas_contract` (`contract_id`),
    CONSTRAINT `fk_cas_contract`
        FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
