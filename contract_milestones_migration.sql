-- Migration: contract milestone types and contract milestones tables

CREATE TABLE IF NOT EXISTS `contract_milestone_types` (
  `milestone_type_id`  int          NOT NULL AUTO_INCREMENT,
  `name`               varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order`         smallint     NOT NULL DEFAULT 0,
  `is_active`          tinyint(1)   NOT NULL DEFAULT 1,
  `created_at`         timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_type_id`),
  UNIQUE KEY `uq_milestone_type_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contract_milestones` (
  `milestone_id`        int          NOT NULL AUTO_INCREMENT,
  `contract_id`         int          NOT NULL,
  `milestone_type_id`   int          NOT NULL,
  `milestone_date`      date         NOT NULL,
  `notes`               text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_person_id` int         DEFAULT NULL,
  `created_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_id`),
  KEY `idx_cm_contract_id` (`contract_id`),
  KEY `idx_cm_type_id` (`milestone_type_id`),
  CONSTRAINT `fk_cm_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_type`     FOREIGN KEY (`milestone_type_id`) REFERENCES `contract_milestone_types` (`milestone_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
