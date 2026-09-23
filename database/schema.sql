-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: contract_manager
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `approval_rules`
--

DROP TABLE IF EXISTS `approval_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_rules` (
  `rule_id` int NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-readable label, e.g. "Manager approval over $30k"',
  `contract_field` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contract field to evaluate, e.g. total_contract_value',
  `operator` enum('>','>=','<','<=','=','!=') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '>',
  `threshold_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Value to compare against (stored as string)',
  `required_approval` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `waived_by_standard_contract` tinyint(1) NOT NULL DEFAULT '0',
  `waived_by_min_insurance` tinyint(1) NOT NULL DEFAULT '0',
  `contract_field_2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operator_2` enum('>','>=','<','<=','=','!=') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `threshold_value_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `idx_approval_rules_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bidding_compliance`
--

DROP TABLE IF EXISTS `bidding_compliance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bidding_compliance` (
  `compliance_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `event_date` date NOT NULL,
  `event_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_consortium` tinyint(1) NOT NULL DEFAULT '0',
  `consortium_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consortium_contract_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_document_id` int DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`compliance_id`),
  KEY `idx_bc_contract_id` (`contract_id`),
  KEY `fk_bc_document` (`contract_document_id`),
  KEY `fk_bc_person` (`created_by_person_id`),
  CONSTRAINT `fk_bc_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bc_document` FOREIGN KEY (`contract_document_id`) REFERENCES `contract_documents` (`contract_document_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bc_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bidding_compliance_event_types`
--

DROP TABLE IF EXISTS `bidding_compliance_event_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bidding_compliance_event_types` (
  `event_type_id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_type_id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `change_orders`
--

DROP TABLE IF EXISTS `change_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `change_orders` (
  `change_order_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `change_order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `co_justification` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `co_amount` decimal(15,2) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_order_id`),
  KEY `idx_co_contract_id` (`contract_id`),
  CONSTRAINT `fk_co_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `company_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('internal','customer','vendor','partner','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vendor',
  `company_type_id` int DEFAULT NULL,
  `tax_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_region` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_of_incorporation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coi_exp_date` date DEFAULT NULL,
  `coi_carrier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coi_verified_by_person_id` int DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sosid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NC Secretary of State ID',
  `signer1_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer1_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer1_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer2_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer2_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer2_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer3_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer3_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signer3_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`company_id`),
  KEY `fk_companies_company_type` (`company_type_id`),
  KEY `idx_companies_coi_verified_by` (`coi_verified_by_person_id`),
  CONSTRAINT `fk_companies_coi_verified_by` FOREIGN KEY (`coi_verified_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_companies_company_type` FOREIGN KEY (`company_type_id`) REFERENCES `company_types` (`company_type_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=576 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `company_comments`
--

DROP TABLE IF EXISTS `company_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_comments` (
  `company_comment_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `person_id` int NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`company_comment_id`),
  KEY `idx_company` (`company_id`),
  KEY `idx_person` (`person_id`),
  CONSTRAINT `fk_company_comments_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_company_comments_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `company_types`
--

DROP TABLE IF EXISTS `company_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_types` (
  `company_type_id` int NOT NULL AUTO_INCREMENT,
  `company_type` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`company_type_id`),
  UNIQUE KEY `company_type` (`company_type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_approval_overrides`
--

DROP TABLE IF EXISTS `contract_approval_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_approval_overrides` (
  `override_id` int unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int unsigned NOT NULL,
  `approval_type` varchar(50) NOT NULL,
  `added_by_person_id` int unsigned DEFAULT NULL,
  `added_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`override_id`),
  UNIQUE KEY `uq_contract_approval` (`contract_id`,`approval_type`),
  KEY `idx_contract_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_approval_stamps`
--

DROP TABLE IF EXISTS `contract_approval_stamps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_approval_stamps` (
  `stamp_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `approval_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stamp_date` date NOT NULL,
  `stamped_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`stamp_id`),
  UNIQUE KEY `uq_contract_approval` (`contract_id`,`approval_key`),
  KEY `idx_cas_contract` (`contract_id`),
  CONSTRAINT `fk_cas_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_body_versions`
--

DROP TABLE IF EXISTS `contract_body_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_body_versions` (
  `contract_body_version_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `body_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contract_body_version_id`),
  KEY `contract_id` (`contract_id`),
  KEY `fk_cbv_person` (`created_by_person_id`),
  CONSTRAINT `fk_cbv_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cbv_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_document_revisions`
--

DROP TABLE IF EXISTS `contract_document_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_document_revisions` (
  `revision_id` int NOT NULL AUTO_INCREMENT,
  `contract_document_id` int NOT NULL,
  `contract_id` int NOT NULL,
  `version` int NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_sha256` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`revision_id`),
  UNIQUE KEY `uniq_doc_version` (`contract_document_id`,`version`),
  KEY `contract_document_id` (`contract_document_id`),
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `fk_rev_doc` FOREIGN KEY (`contract_document_id`) REFERENCES `contract_documents` (`contract_document_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_documents`
--

DROP TABLE IF EXISTS `contract_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_documents` (
  `contract_document_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `change_order_id` int DEFAULT NULL,
  `doc_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generated_contract',
  `exhibit_label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  `storage_provider` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `external_document_id` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_drive_id` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_site_id` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_web_url` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_word_url` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_path` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_version` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_modified_at` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_modified_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sync_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sync_error` text COLLATE utf8mb4_unicode_ci,
  `docusign_envelope_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docusign_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docusign_sent_at` timestamp NULL DEFAULT NULL,
  `docusign_completed_at` timestamp NULL DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contract_document_id`),
  KEY `fk_contract_documents_user` (`created_by_person_id`),
  KEY `idx_contract_documents_contract_id` (`contract_id`),
  KEY `idx_contract_documents_storage_provider` (`storage_provider`),
  KEY `idx_contract_documents_external_document_id` (`external_document_id`),
  KEY `idx_contract_documents_change_order_id` (`change_order_id`),
  CONSTRAINT `fk_contract_documents_change_order` FOREIGN KEY (`change_order_id`) REFERENCES `change_orders` (`change_order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contract_documents_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contract_documents_person` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=872 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_exhibits`
--

DROP TABLE IF EXISTS `contract_exhibits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_exhibits` (
  `exhibit_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `exhibit_label` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int NOT NULL,
  `sha256` char(64) NOT NULL,
  `pdf_blob` longblob NOT NULL,
  `uploaded_by_person_id` int NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `exhibit_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`exhibit_id`),
  KEY `idx_contract` (`contract_id`),
  CONSTRAINT `fk_exhibits_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_html_revisions`
--

DROP TABLE IF EXISTS `contract_html_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_html_revisions` (
  `revision_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `document_id` int DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `old_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `new_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `accepted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`revision_id`),
  KEY `idx_chr_contract` (`contract_id`,`created_at`),
  KEY `idx_chr_doc` (`document_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_intake_exhibits`
--

DROP TABLE IF EXISTS `contract_intake_exhibits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_intake_exhibits` (
  `exhibit_id` int NOT NULL AUTO_INCREMENT,
  `submission_id` int NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID hex + .bin stored in storage/intake_exhibits/',
  `file_size` int NOT NULL DEFAULT '0' COMMENT 'bytes',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `doc_category` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `scan_status` enum('pending','clean','infected','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `scan_output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`exhibit_id`),
  KEY `idx_cie_submission` (`submission_id`),
  CONSTRAINT `fk_cie_submission` FOREIGN KEY (`submission_id`) REFERENCES `contract_intake_submissions` (`submission_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_intake_submissions`
--

DROP TABLE IF EXISTS `contract_intake_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_intake_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `submitter_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitter_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitter_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_department` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_person_id` int DEFAULT NULL,
  `responsible_person_id` int DEFAULT NULL,
  `contract_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_description` text COLLATE utf8mb4_unicode_ci,
  `contract_type_id` int DEFAULT NULL,
  `counterparty_company` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_value` decimal(15,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `po_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `counterparty_signer1_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer1_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer1_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer2_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer2_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer2_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer3_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer3_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_signer3_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esign_consent` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending','imported','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `imported_contract_id` int DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`submission_id`),
  KEY `idx_intake_submitter_person` (`submitter_person_id`),
  KEY `idx_intake_responsible_person` (`responsible_person_id`),
  CONSTRAINT `fk_intake_responsible_person` FOREIGN KEY (`responsible_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_intake_submitter_person` FOREIGN KEY (`submitter_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_milestone_types`
--

DROP TABLE IF EXISTS `contract_milestone_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_milestone_types` (
  `milestone_type_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_type_id`),
  UNIQUE KEY `uq_milestone_type_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_milestones`
--

DROP TABLE IF EXISTS `contract_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_milestones` (
  `milestone_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `milestone_type_id` int NOT NULL,
  `milestone_date` date NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_id`),
  KEY `idx_cm_contract_id` (`contract_id`),
  KEY `idx_cm_type_id` (`milestone_type_id`),
  CONSTRAINT `fk_cm_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cm_type` FOREIGN KEY (`milestone_type_id`) REFERENCES `contract_milestone_types` (`milestone_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_status_history`
--

DROP TABLE IF EXISTS `contract_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_status_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `event_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'status_change',
  `old_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_by` int DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`history_id`),
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `contract_status_history_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1562 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_statuses`
--

DROP TABLE IF EXISTS `contract_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_statuses` (
  `contract_status_id` int NOT NULL AUTO_INCREMENT,
  `contract_status_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_status_desc` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`contract_status_id`),
  UNIQUE KEY `contract_status_name_UNIQUE` (`contract_status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_types`
--

DROP TABLE IF EXISTS `contract_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_types` (
  `contract_type_id` int NOT NULL AUTO_INCREMENT,
  `contract_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `formal_bidding_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `template_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_file_docx` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_file_html` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_id` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`contract_type_id`),
  UNIQUE KEY `contract_type` (`contract_type`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contracts` (
  `contract_id` int NOT NULL AUTO_INCREMENT,
  `contract_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_status_id` int DEFAULT NULL,
  `status_comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `governing_law` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'North Carolina',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `use_standard_contract` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_insurance_coi` tinyint(1) NOT NULL DEFAULT '0',
  `date_approved_by_procurement` date DEFAULT NULL,
  `date_approved_by_manager` date DEFAULT NULL,
  `date_approved_by_council` date DEFAULT NULL,
  `renewal_term_months` int DEFAULT NULL,
  `total_contract_value` decimal(18,2) DEFAULT NULL,
  `manager_approval_date` date DEFAULT NULL,
  `purchasing_approval_date` date DEFAULT NULL,
  `legal_approval_date` date DEFAULT NULL,
  `risk_manager_approval_date` date DEFAULT NULL,
  `council_approval_date` date DEFAULT NULL,
  `po_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `po_amount` decimal(15,2) DEFAULT NULL,
  `currency` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'USD',
  `owner_company_id` int DEFAULT '3',
  `counterparty_company_id` int DEFAULT NULL,
  `owner_primary_contact_id` int DEFAULT NULL,
  `submitted_by_person_id` int DEFAULT NULL,
  `town_location_id` int DEFAULT NULL,
  `counterparty_primary_contact_id` int DEFAULT NULL,
  `counterparty_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `counterparty_contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `documents_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `procurement_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `procurement_method_id` int DEFAULT NULL,
  `bid_rfp_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bid_documents_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `procurement_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `department_id` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `parent_contract_id` int DEFAULT NULL,
  `contract_type_id` int DEFAULT NULL,
  `contract_body_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_terms_id` int DEFAULT '1',
  `is_imported` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contract_id`),
  UNIQUE KEY `contract_number` (`contract_number`),
  KEY `idx_contracts_status` (`contract_status_id`),
  KEY `idx_contracts_end_date` (`end_date`),
  KEY `idx_contracts_owner` (`owner_company_id`),
  KEY `idx_contracts_counterparty` (`counterparty_company_id`),
  KEY `idx_contracts_department_id` (`department_id`),
  KEY `idx_contracts_contract_type_id` (`contract_type_id`),
  KEY `fk_contract_owner_contact` (`owner_primary_contact_id`),
  KEY `fk_contract_counterparty_contact` (`counterparty_primary_contact_id`),
  KEY `fk_contracts_payment_terms` (`payment_terms_id`),
  KEY `idx_contracts_project_id` (`project_id`),
  KEY `idx_contracts_town_location_id` (`town_location_id`),
  KEY `fk_contracts_procurement_method` (`procurement_method_id`),
  KEY `idx_contracts_parent_contract_id` (`parent_contract_id`),
  KEY `idx_contracts_submitted_by` (`submitted_by_person_id`),
  CONSTRAINT `fk_contract_counterparty` FOREIGN KEY (`counterparty_company_id`) REFERENCES `companies` (`company_id`),
  CONSTRAINT `fk_contract_counterparty_contact` FOREIGN KEY (`counterparty_primary_contact_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contract_owner` FOREIGN KEY (`owner_company_id`) REFERENCES `companies` (`company_id`),
  CONSTRAINT `fk_contract_owner_contact` FOREIGN KEY (`owner_primary_contact_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contract_payment_terms` FOREIGN KEY (`payment_terms_id`) REFERENCES `payment_terms` (`payment_terms_id`),
  CONSTRAINT `fk_contracts_contract_type` FOREIGN KEY (`contract_type_id`) REFERENCES `contract_types` (`contract_type_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_parent_contract` FOREIGN KEY (`parent_contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_payment_terms` FOREIGN KEY (`payment_terms_id`) REFERENCES `payment_terms` (`payment_terms_id`),
  CONSTRAINT `fk_contracts_procurement_method` FOREIGN KEY (`procurement_method_id`) REFERENCES `procurement_methods` (`procurement_method_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_submitted_by` FOREIGN KEY (`submitted_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_town_location` FOREIGN KEY (`town_location_id`) REFERENCES `town_locations` (`location_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=843 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `department_id` int NOT NULL AUTO_INCREMENT,
  `department_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dept_initials` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `department_head_id` int DEFAULT NULL,
  `assistant_town_manager_id` int DEFAULT NULL,
  `contract_admin_id` int DEFAULT NULL,
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_code` (`department_code`),
  KEY `fk_departments_contract_admin` (`contract_admin_id`),
  CONSTRAINT `fk_departments_contract_admin` FOREIGN KEY (`contract_admin_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `development_agreement_submissions`
--

DROP TABLE IF EXISTS `development_agreement_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `development_agreement_submissions` (
  `submission_id` int NOT NULL AUTO_INCREMENT,
  `submitter_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_company` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_owner_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_entity_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_contact_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_state_of_incorporation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `project_description` text COLLATE utf8mb4_unicode_ci,
  `proposed_improvements` text COLLATE utf8mb4_unicode_ci,
  `current_zoning` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposed_zoning` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comp_plan_designation` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anticipated_start_date` date DEFAULT NULL,
  `anticipated_end_date` date DEFAULT NULL,
  `agreement_termination_date` date DEFAULT NULL,
  `planning_board_date` date DEFAULT NULL,
  `town_council_hearing_date` date DEFAULT NULL,
  `tracts_json` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','imported','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `imported_dev_agreement_id` int DEFAULT NULL,
  `review_notes` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`submission_id`),
  KEY `idx_sub_status` (`status`),
  KEY `idx_sub_submitted` (`submitted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `development_agreement_tracts`
--

DROP TABLE IF EXISTS `development_agreement_tracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `development_agreement_tracts` (
  `tract_id` int NOT NULL AUTO_INCREMENT,
  `dev_agreement_id` int NOT NULL,
  `property_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_pin` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_realestateid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_acerage` decimal(10,4) DEFAULT NULL,
  `owner_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tract_id`),
  KEY `fk_tract_devagr` (`dev_agreement_id`),
  CONSTRAINT `fk_tract_devagr` FOREIGN KEY (`dev_agreement_id`) REFERENCES `development_agreements` (`dev_agreement_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `development_agreements`
--

DROP TABLE IF EXISTS `development_agreements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `development_agreements` (
  `dev_agreement_id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int DEFAULT NULL,
  `attorney_id` int DEFAULT NULL,
  `property_owner_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_entity_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_contact_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_state_of_incorporation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `developer_entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_pin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `property_realestateid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `project_description` longtext COLLATE utf8mb4_unicode_ci,
  `property_acerage` decimal(10,4) DEFAULT NULL,
  `current_zoning` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposed_zoning` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comp_plan_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anticipated_start_date` date DEFAULT NULL,
  `anticipated_end_date` date DEFAULT NULL,
  `proposed_improvements` longtext COLLATE utf8mb4_unicode_ci,
  `agreement_termination_date` date DEFAULT NULL,
  `planning_board_date` date DEFAULT NULL,
  `town_council_hearing_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `number_of_units` int DEFAULT NULL COMMENT 'Number of single-family homes / ERUs',
  `daily_flow_maximum` int DEFAULT NULL COMMENT 'Maximum daily flow in gallons per day',
  `allocation_elements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'List of allocation elements',
  `parkland_dedication` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Parkland dedication required (boolean)',
  `transportation_tier` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tier 1 | Tier 2 | Tier 3',
  PRIMARY KEY (`dev_agreement_id`),
  KEY `fk_devagr_attorney` (`attorney_id`),
  KEY `fk_devagr_contract` (`contract_id`),
  CONSTRAINT `fk_devagr_attorney` FOREIGN KEY (`attorney_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_devagr_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `document_categories`
--

DROP TABLE IF EXISTS `document_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_key` (`category_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `legal_matters`
--

DROP TABLE IF EXISTS `legal_matters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `legal_matters` (
  `legal_matter_id` int NOT NULL AUTO_INCREMENT,
  `file_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matter_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assigned_to` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `requestedby_id` int DEFAULT NULL,
  `assigned_to_person_id` int DEFAULT NULL,
  `status` enum('New','In Progress','Under Review','Pending Council/Approval','On Hold','Completed','Closed','Cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'New',
  `date_started` date DEFAULT NULL,
  `date_due` date DEFAULT NULL,
  `date_closed` date DEFAULT NULL,
  `matter_long_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue1_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `issue2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue2_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `council_update_date` date DEFAULT NULL,
  `council_update_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tasks_needed` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_completed` date DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `active_lawsuit` tinyint(1) DEFAULT '0',
  `nclm_claim_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `engineering_flag` tinyint(1) DEFAULT '0',
  `public_safety_flag` tinyint(1) DEFAULT '0',
  `file_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_label` tinyint(1) DEFAULT '0',
  `archive_flag` tinyint(1) DEFAULT '0',
  `requested_by_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planning_flag` tinyint(1) DEFAULT '0',
  `administration_flag` tinyint(1) DEFAULT '0',
  `public_works_flag` tinyint(1) DEFAULT '0',
  `hr_flag` tinyint(1) DEFAULT '0',
  `public_utilities_flag` tinyint(1) DEFAULT '0',
  `parks_rec_flag` tinyint(1) DEFAULT '0',
  `finance_flag` tinyint(1) DEFAULT '0',
  `inspections_flag` tinyint(1) DEFAULT '0',
  `economic_flag` tinyint(1) DEFAULT '0',
  `it_flag` tinyint(1) DEFAULT '0',
  `clerk_pio_flag` tinyint(1) DEFAULT '0',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UNC path to supporting document(s), e.g. \\serversharefolderfile.pdf',
  PRIMARY KEY (`legal_matter_id`),
  UNIQUE KEY `file_number` (`file_number`),
  KEY `idx_file_number` (`file_number`),
  KEY `idx_status` (`status`),
  KEY `idx_date_started` (`date_started`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `assigned_to_person_id` (`assigned_to_person_id`),
  KEY `department_id` (`department_id`),
  KEY `requestedby_id` (`requestedby_id`),
  CONSTRAINT `legal_matters_ibfk_1` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `legal_matters_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `legal_matters_ibfk_3` FOREIGN KEY (`requestedby_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18893 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organization_settings`
--

DROP TABLE IF EXISTS `organization_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `org_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `org_type` enum('city','county','town') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `finance_director_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mayor_or_exec_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `town_manager_person_id` int DEFAULT NULL,
  `town_clerk_person_id` int DEFAULT NULL,
  `town_attorney_person_id` int DEFAULT NULL,
  `finance_director_person_id` int DEFAULT NULL,
  `fiscal_year_start_month` tinyint unsigned NOT NULL DEFAULT '7',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_org_town_manager` (`town_manager_person_id`),
  KEY `fk_org_town_clerk` (`town_clerk_person_id`),
  KEY `fk_org_town_attorney` (`town_attorney_person_id`),
  KEY `fk_org_finance_director` (`finance_director_person_id`),
  CONSTRAINT `fk_org_finance_director` FOREIGN KEY (`finance_director_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_org_town_attorney` FOREIGN KEY (`town_attorney_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_org_town_clerk` FOREIGN KEY (`town_clerk_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_org_town_manager` FOREIGN KEY (`town_manager_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `password_reset_id` int NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `token_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `requested_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`password_reset_id`),
  UNIQUE KEY `uq_password_resets_token_hash` (`token_hash`),
  KEY `idx_password_resets_person` (`person_id`),
  KEY `idx_password_resets_expires` (`expires_at`),
  CONSTRAINT `fk_password_resets_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_terms`
--

DROP TABLE IF EXISTS `payment_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_terms` (
  `payment_terms_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`payment_terms_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `people` (
  `person_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(`first_name`,_utf8mb4' ',`last_name`)) STORED,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nextcloud_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nextcloud_password` text COLLATE utf8mb4_unicode_ci,
  `officephone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cellphone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_town_employee` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `department_id` int DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `can_login` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `uq_people_email` (`email`),
  KEY `fk_people_company` (`company_id`),
  KEY `fk_people_department` (`department_id`),
  CONSTRAINT `fk_people_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_people_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_department_roles`
--

DROP TABLE IF EXISTS `person_department_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_department_roles` (
  `person_id` int NOT NULL,
  `department_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`,`department_id`,`role_id`),
  KEY `fk_pdr_dept` (`department_id`),
  KEY `fk_pdr_role` (`role_id`),
  CONSTRAINT `fk_pdr_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pdr_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pdr_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_login_events`
--

DROP TABLE IF EXISTS `person_login_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_login_events` (
  `login_event_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `logged_in_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`login_event_id`),
  KEY `idx_ple_person_logged_in_at` (`person_id`,`logged_in_at`),
  CONSTRAINT `fk_ple_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=370 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_roles`
--

DROP TABLE IF EXISTS `person_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_roles` (
  `person_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`,`role_id`),
  KEY `fk_person_roles_role` (`role_id`),
  CONSTRAINT `fk_person_roles_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_person_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pm_settings`
--

DROP TABLE IF EXISTS `pm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pm_settings` (
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procurement_methods`
--

DROP TABLE IF EXISTS `procurement_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procurement_methods` (
  `procurement_method_id` int NOT NULL AUTO_INCREMENT,
  `short_desc` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `long_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  PRIMARY KEY (`procurement_method_id`),
  UNIQUE KEY `short_desc` (`short_desc`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_budget_lines`
--

DROP TABLE IF EXISTS `project_budget_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_budget_lines` (
  `budget_line_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `line_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiscal_year` smallint DEFAULT NULL,
  `budgeted_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `committed_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `actual_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `funding_source_id` int DEFAULT NULL,
  `contract_id` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`budget_line_id`),
  KEY `idx_pbl_project` (`project_id`),
  KEY `idx_pbl_funding_source` (`funding_source_id`),
  KEY `idx_pbl_contract` (`contract_id`),
  CONSTRAINT `fk_pbl_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pbl_funding_source` FOREIGN KEY (`funding_source_id`) REFERENCES `project_funding_sources` (`funding_source_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pbl_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_document_types`
--

DROP TABLE IF EXISTS `project_document_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_document_types` (
  `document_type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`document_type_id`),
  UNIQUE KEY `uq_pdt_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_documents`
--

DROP TABLE IF EXISTS `project_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_documents` (
  `project_document_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `doc_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_document_id`),
  KEY `idx_pd_project` (`project_id`),
  KEY `fk_pd_uploaded_by` (`uploaded_by_person_id`),
  CONSTRAINT `fk_pd_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pd_uploaded_by` FOREIGN KEY (`uploaded_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_funding_source_types`
--

DROP TABLE IF EXISTS `project_funding_source_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_funding_source_types` (
  `funding_source_type_id` int NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`funding_source_type_id`),
  UNIQUE KEY `uq_pfst_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_funding_sources`
--

DROP TABLE IF EXISTS `project_funding_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_funding_sources` (
  `funding_source_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `source_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `grant_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `awarded_amount` decimal(18,2) DEFAULT NULL,
  `received_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` enum('anticipated','awarded','received','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anticipated',
  `expiration_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`funding_source_id`),
  KEY `idx_pfs_project` (`project_id`),
  CONSTRAINT `fk_pfs_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_meeting_attendees`
--

DROP TABLE IF EXISTS `project_meeting_attendees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_meeting_attendees` (
  `meeting_id` int NOT NULL,
  `person_id` int NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`meeting_id`,`person_id`),
  KEY `idx_pma_person` (`person_id`),
  CONSTRAINT `fk_pma_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `project_meetings` (`meeting_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pma_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_meetings`
--

DROP TABLE IF EXISTS `project_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_meetings` (
  `meeting_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agenda` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `minutes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`meeting_id`),
  KEY `idx_pm_project` (`project_id`),
  CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_notes`
--

DROP TABLE IF EXISTS `project_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_notes` (
  `note_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `person_id` int DEFAULT NULL,
  `note_date` date NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`),
  KEY `idx_project_notes_project` (`project_id`),
  KEY `fk_project_notes_person` (`person_id`),
  CONSTRAINT `fk_project_notes_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_project_notes_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_priorities`
--

DROP TABLE IF EXISTS `project_priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_priorities` (
  `project_priority_id` int NOT NULL AUTO_INCREMENT,
  `priority_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_priority_id`),
  UNIQUE KEY `uq_project_priorities_name` (`priority_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_risks`
--

DROP TABLE IF EXISTS `project_risks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_risks` (
  `risk_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `likelihood` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `impact` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('open','mitigating','closed','realized') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `owner_person_id` int DEFAULT NULL,
  `identified_date` date DEFAULT NULL,
  `mitigation_plan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `review_date` date DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`risk_id`),
  KEY `idx_pr_project` (`project_id`),
  KEY `idx_pr_owner` (`owner_person_id`),
  CONSTRAINT `fk_pr_owner` FOREIGN KEY (`owner_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_statuses`
--

DROP TABLE IF EXISTS `project_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_statuses` (
  `project_status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_status_id`),
  UNIQUE KEY `uq_project_statuses_name` (`status_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_tasks`
--

DROP TABLE IF EXISTS `project_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `parent_task_id` int DEFAULT NULL,
  `task_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('not_started','in_progress','blocked','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_started',
  `priority` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `dependency_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'independent',
  `depends_on_task_id` int DEFAULT NULL,
  `assigned_to_person_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_pt_project` (`project_id`),
  KEY `idx_pt_parent` (`parent_task_id`),
  KEY `idx_pt_assignee` (`assigned_to_person_id`),
  KEY `idx_pt_depends_on` (`depends_on_task_id`),
  CONSTRAINT `fk_pt_assignee` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_depends_on` FOREIGN KEY (`depends_on_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `project_tasks` (`task_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_team_members`
--

DROP TABLE IF EXISTS `project_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_team_members` (
  `project_id` int NOT NULL,
  `person_id` int NOT NULL,
  `project_role` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_lead` tinyint(1) NOT NULL DEFAULT '0',
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`,`person_id`),
  KEY `idx_ptm_person` (`person_id`),
  CONSTRAINT `fk_ptm_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_timeline_milestones`
--

DROP TABLE IF EXISTS `project_timeline_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_timeline_milestones` (
  `milestone_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `milestone_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `target_date` date DEFAULT NULL,
  `actual_date` date DEFAULT NULL,
  `status` enum('pending','on_track','at_risk','delayed','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sort_order` smallint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`milestone_id`),
  KEY `idx_ptl_project` (`project_id`),
  CONSTRAINT `fk_ptl_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_type_default_tasks`
--

DROP TABLE IF EXISTS `project_type_default_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_type_default_tasks` (
  `default_task_id` int NOT NULL AUTO_INCREMENT,
  `project_type_id` int NOT NULL,
  `task_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`default_task_id`),
  KEY `idx_ptdt_type` (`project_type_id`),
  CONSTRAINT `fk_ptdt_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `project_types`
--

DROP TABLE IF EXISTS `project_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_types` (
  `project_type_id` int NOT NULL AUTO_INCREMENT,
  `project_type_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_type_id`),
  UNIQUE KEY `uq_project_types_name` (`project_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_type_id` int DEFAULT NULL,
  `project_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proposed',
  `priority` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `department_id` int DEFAULT NULL,
  `project_manager_person_id` int DEFAULT NULL,
  `sponsor_person_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `target_end_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `estimated_budget` decimal(18,2) DEFAULT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `uq_projects_code` (`project_code`),
  KEY `idx_projects_department` (`department_id`),
  KEY `idx_projects_status` (`status`),
  KEY `fk_projects_pm` (`project_manager_person_id`),
  KEY `fk_projects_sponsor` (`sponsor_person_id`),
  KEY `fk_projects_created_by` (`created_by_person_id`),
  KEY `idx_projects_type` (`project_type_id`),
  CONSTRAINT `fk_projects_created_by` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_pm` FOREIGN KEY (`project_manager_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_sponsor` FOREIGN KEY (`sponsor_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_projects_type` FOREIGN KEY (`project_type_id`) REFERENCES `project_types` (`project_type_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `approval_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'When set, this role appears in the Approval Rules Required Approval dropdown. Value is stored in approval_rules.required_approval.',
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_key` (`role_key`),
  UNIQUE KEY `uq_roles_approval_key` (`approval_key`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `assigned_to_person_id` int NOT NULL,
  `created_by_person_id` int DEFAULT NULL,
  `contract_id` int DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('open','in_progress','done') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `last_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completion_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_tasks_assigned_to` (`assigned_to_person_id`),
  KEY `idx_tasks_created_by` (`created_by_person_id`),
  KEY `idx_tasks_contract_id` (`contract_id`),
  KEY `idx_tasks_status` (`status`),
  CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to_person_id`) REFERENCES `people` (`person_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by_person_id`) REFERENCES `people` (`person_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `town_locations`
--

DROP TABLE IF EXISTS `town_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `town_locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_region` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'contract_manager'
--

--
-- Dumping routines for database 'contract_manager'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-23 15:53:42
