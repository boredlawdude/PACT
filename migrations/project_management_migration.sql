-- Migration: project_manager_app tables (2.0 project management layer)
-- Adds a `projects` parent entity plus one table per module (Tasks, Risks,
-- Budget, Funding, Meetings, Timeline, Documents, Team), and links the
-- existing `contracts` table in as a child of a project via `project_id`.
--
-- This lives in the SAME database as contracts_app (contract_manager).
-- contracts_app's own code is unaffected except for optionally filtering
-- the contracts list by ?project_id=; no existing columns are altered.

-- ── Parent entity ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id`                 int          NOT NULL AUTO_INCREMENT,
  `project_code`                varchar(50)  COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_name`                varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`                 text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status`                      enum('proposed','active','on_hold','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proposed',
  `priority`                    enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `department_id`               int          DEFAULT NULL,
  `project_manager_person_id`   int          DEFAULT NULL,
  `sponsor_person_id`           int          DEFAULT NULL,
  `start_date`                  date         DEFAULT NULL,
  `target_end_date`             date         DEFAULT NULL,
  `actual_end_date`             date         DEFAULT NULL,
  `estimated_budget`            decimal(18,2) DEFAULT NULL,
  `created_by_person_id`        int          DEFAULT NULL,
  `created_at`                  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `uq_projects_code` (`project_code`),
  KEY `idx_projects_department` (`department_id`),
  KEY `idx_projects_status` (`status`),
  CONSTRAINT `fk_projects_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_pm` FOREIGN KEY (`project_manager_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_sponsor` FOREIGN KEY (`sponsor_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Link Contracts in as a child of a project (additive, nullable) ──────
SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND COLUMN_NAME = 'project_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `contracts` ADD COLUMN `project_id` int DEFAULT NULL AFTER `department_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add the index/FK only if not already present (safe to re-run).
SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'contracts'
    AND CONSTRAINT_NAME = 'fk_contracts_project'
);
SET @sql := IF(@fk_exists = 0,
  'ALTER TABLE `contracts`
     ADD KEY `idx_contracts_project_id` (`project_id`),
     ADD CONSTRAINT `fk_contracts_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── Team ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_team_members` (
  `project_id`   int          NOT NULL,
  `person_id`    int          NOT NULL,
  `project_role` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_lead`      tinyint(1)   NOT NULL DEFAULT 0,
  `added_at`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`,`person_id`),
  KEY `idx_ptm_person` (`person_id`),
  CONSTRAINT `fk_ptm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptm_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tasks ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_tasks` (
  `task_id`              int          NOT NULL AUTO_INCREMENT,
  `project_id`           int          NOT NULL,
  `parent_task_id`       int          DEFAULT NULL,
  `task_name`            varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`          text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status`               enum('not_started','in_progress','blocked','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `priority`             enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `assigned_to_person_id` int         DEFAULT NULL,
  `start_date`           date         DEFAULT NULL,
  `due_date`             date         DEFAULT NULL,
  `completed_at`         timestamp    NULL DEFAULT NULL,
  `sort_order`           smallint     NOT NULL DEFAULT 0,
  `created_by_person_id` int         DEFAULT NULL,
  `created_at`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_pt_project` (`project_id`),
  KEY `idx_pt_parent` (`parent_task_id`),
  KEY `idx_pt_assignee` (`assigned_to_person_id`),
  CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pt_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_assignee` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Risks ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_risks` (
  `risk_id`         int          NOT NULL AUTO_INCREMENT,
  `project_id`      int          NOT NULL,
  `title`           varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`     text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category`        varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `likelihood`      enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `impact`          enum('low','medium','high') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status`          enum('open','mitigating','closed','realized') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `owner_person_id` int         DEFAULT NULL,
  `identified_date` date         DEFAULT NULL,
  `mitigation_plan` text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_date`     date         DEFAULT NULL,
  `closed_at`       timestamp    NULL DEFAULT NULL,
  `created_at`      timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`risk_id`),
  KEY `idx_pr_project` (`project_id`),
  KEY `idx_pr_owner` (`owner_person_id`),
  CONSTRAINT `fk_pr_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pr_owner` FOREIGN KEY (`owner_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Funding (create before Budget since budget lines reference it) ─────
CREATE TABLE IF NOT EXISTS `project_funding_sources` (
  `funding_source_id` int          NOT NULL AUTO_INCREMENT,
  `project_id`        int          NOT NULL,
  `source_name`       varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type`       enum('grant','bond','general_fund','enterprise_fund','impact_fee','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `grant_number`      varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `awarded_amount`    decimal(18,2) DEFAULT NULL,
  `received_amount`   decimal(18,2) NOT NULL DEFAULT 0.00,
  `status`            enum('anticipated','awarded','received','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anticipated',
  `expiration_date`   date         DEFAULT NULL,
  `notes`             text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`funding_source_id`),
  KEY `idx_pfs_project` (`project_id`),
  CONSTRAINT `fk_pfs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Budget ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_budget_lines` (
  `budget_line_id`    int          NOT NULL AUTO_INCREMENT,
  `project_id`        int          NOT NULL,
  `line_name`         varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category`          varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiscal_year`       smallint    DEFAULT NULL,
  `budgeted_amount`   decimal(18,2) NOT NULL DEFAULT 0.00,
  `committed_amount`  decimal(18,2) NOT NULL DEFAULT 0.00,
  `actual_amount`     decimal(18,2) NOT NULL DEFAULT 0.00,
  `funding_source_id` int         DEFAULT NULL,
  `contract_id`       int         DEFAULT NULL,
  `notes`             text        COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at`        timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`budget_line_id`),
  KEY `idx_pbl_project` (`project_id`),
  KEY `idx_pbl_funding_source` (`funding_source_id`),
  KEY `idx_pbl_contract` (`contract_id`),
  CONSTRAINT `fk_pbl_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pbl_funding_source` FOREIGN KEY (`funding_source_id`) REFERENCES `project_funding_sources` (`funding_source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pbl_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Meetings ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_meetings` (
  `meeting_id`           int          NOT NULL AUTO_INCREMENT,
  `project_id`           int          NOT NULL,
  `meeting_date`         datetime     NOT NULL,
  `meeting_type`         varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location`             varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agenda`               text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minutes`              text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_person_id` int         DEFAULT NULL,
  `created_at`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`meeting_id`),
  KEY `idx_pm_project` (`project_id`),
  CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `project_meeting_attendees` (
  `meeting_id` int        NOT NULL,
  `person_id`  int        NOT NULL,
  `attended`   tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`meeting_id`,`person_id`),
  KEY `idx_pma_person` (`person_id`),
  CONSTRAINT `fk_pma_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `project_meetings` (`meeting_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pma_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Timeline ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `project_timeline_milestones` (
  `milestone_id`   int          NOT NULL AUTO_INCREMENT,
  `project_id`     int          NOT NULL,
  `milestone_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description`    text         COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_date`    date         DEFAULT NULL,
  `actual_date`    date         DEFAULT NULL,
  `status`         enum('pending','on_track','at_risk','delayed','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sort_order`     smallint     NOT NULL DEFAULT 0,
  `created_at`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_id`),
  KEY `idx_ptl_project` (`project_id`)
  , CONSTRAINT `fk_ptl_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Documents (project-level, separate from contract_documents) ────────
CREATE TABLE IF NOT EXISTS `project_documents` (
  `project_document_id`  int          NOT NULL AUTO_INCREMENT,
  `project_id`           int          NOT NULL,
  `doc_type`             varchar(50)  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `file_name`            varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path`            varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type`            varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_person_id` int         DEFAULT NULL,
  `created_at`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_document_id`),
  KEY `idx_pd_project` (`project_id`),
  CONSTRAINT `fk_pd_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pd_uploaded_by` FOREIGN KEY (`uploaded_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
