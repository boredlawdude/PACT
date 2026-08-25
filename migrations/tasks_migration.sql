-- Migration: general task assignment system (assign tasks to users, optional contract link)

CREATE TABLE IF NOT EXISTS `tasks` (
  `task_id`                int          NOT NULL AUTO_INCREMENT,
  `title`                  varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`            text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to_person_id`  int          NOT NULL,
  `created_by_person_id`   int          DEFAULT NULL,
  `contract_id`            int          DEFAULT NULL,
  `due_date`               date         DEFAULT NULL,
  `status`                 enum('open','in_progress','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `last_reminder_sent_at`  timestamp    NULL DEFAULT NULL,
  `completed_at`           timestamp    NULL DEFAULT NULL,
  `created_at`             timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`             timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_tasks_assigned_to` (`assigned_to_person_id`),
  KEY `idx_tasks_created_by` (`created_by_person_id`),
  KEY `idx_tasks_contract_id` (`contract_id`),
  KEY `idx_tasks_status` (`status`),
  CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_created_by`  FOREIGN KEY (`created_by_person_id`)  REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tasks_contract`    FOREIGN KEY (`contract_id`)           REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
