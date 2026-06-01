-- Migration: contract_intake_exhibits
-- Stores files uploaded by the public during a contract request submission.
-- Files are held in storage/intake_exhibits/ (not web-accessible).
-- scan_status tracks ClamAV result: pending = not yet scanned, clean = OK,
--   infected = malware detected, error = scan tool failed.

CREATE TABLE IF NOT EXISTS `contract_intake_exhibits` (
  `exhibit_id`        int          NOT NULL AUTO_INCREMENT,
  `submission_id`     int          NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename`   varchar(64)  COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID hex + .bin stored in storage/intake_exhibits/',
  `file_size`         int          NOT NULL DEFAULT 0 COMMENT 'bytes',
  `mime_type`         varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scan_status`       enum('pending','clean','infected','error') NOT NULL DEFAULT 'pending',
  `scan_output`       text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at`       timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exhibit_id`),
  KEY `idx_cie_submission` (`submission_id`),
  CONSTRAINT `fk_cie_submission`
    FOREIGN KEY (`submission_id`)
    REFERENCES `contract_intake_submissions` (`submission_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
