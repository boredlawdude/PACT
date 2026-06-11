-- Organization Settings Migration
-- Creates the organization_settings table for the contracts_app

CREATE TABLE IF NOT EXISTS `organization_settings` (
  `id`                      int          NOT NULL AUTO_INCREMENT,
  `org_name`                varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `org_type`                enum('city','county','town') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url`             varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path`               varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_contact_name`    varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_contact_email`   varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finance_director_name`   varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mayor_or_exec_name`      varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiscal_year_start_month` tinyint unsigned NOT NULL DEFAULT '7',
  `updated_at`              datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
