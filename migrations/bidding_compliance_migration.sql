-- Add bidding_compliance table
CREATE TABLE IF NOT EXISTS `bidding_compliance` (
  `compliance_id`        int NOT NULL AUTO_INCREMENT,
  `contract_id`          int NOT NULL,
  `event_date`           date NOT NULL,
  `event_type`           varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment`              text COLLATE utf8mb4_unicode_ci,
  `contract_document_id` int DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at`           timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`compliance_id`),
  KEY `idx_bc_contract_id` (`contract_id`),
  CONSTRAINT `fk_bc_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bc_document` FOREIGN KEY (`contract_document_id`) REFERENCES `contract_documents` (`contract_document_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bc_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
