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
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (1,'SUPERUSER','SuperUser','Full system access (includes admin)',1,NULL);
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (2,'ADMIN','Admin','System administrator',1,NULL);
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (3,'DEPT_CONTRACT_ADMIN','Dept Contract Admin','kick of contract requests, add fields to database',1,'dept_admin');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (4,'DEPT_ADMIN','Dept Administrator','administer & approve contracts for your department, making sure business terms meet the objectives',1,'dept_director');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (5,'TOWN_USER','Town User','Standard internal staff',1,NULL);
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (6,'PUBLIC_USER','Public User','External / public access (read-only, if enabled)',1,'public_user');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (7,'LEGAL_ADMIN','Legal Admin','ensure contract language meets town requirements, using standard contracts when possible',1,'legal');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (8,'PROCUREMENT','Procurement','Review and sign off if bidding was done properly & town receives the best business terms',1,'purchasing');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (9,'TOWN_MANAGER','Town Manager (or Assist)','Approve contracts and budgetary matters',1,'manager');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (10,'RISK_MANAGER','Risk Manager','approve any variance to Town standard insurance requirements',1,'risk_manager');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (11,'TOWN_COUNCIL','Town Council','governing body- approve formal contracts',1,'council');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (12,'TOWN_ATTORNEY','Town Attorney','approve form and terms of contract, but not necessarily the business terms or value',1,'town_attorney');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (13,'TOWN_CLERK','Town Clerk','witness manager or mayor signature, ensure records are kept',1,'town_clerk');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (14,'FINANCE_DIRECTOR','Finance Director','sign all contracts with pre-audit statement',1,'finance_director');
INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`, `is_active`, `approval_key`) VALUES (15,'IT','IT Director','Director of IT - Required for software approvals (including click-through)',1,'it');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `contract_statuses`
--

LOCK TABLES `contract_statuses` WRITE;
/*!40000 ALTER TABLE `contract_statuses` DISABLE KEYS */;
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (1,'Draft/Negotiate','Initial draft of the contract',1);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (7,'Out For Signature','Contract is out for signature',8);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (8,'Executed','Contract has been fully executed',9);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (9,'Executed, Work Started','Contract has been fully executed and a Notice to Proceed is given',10);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (11,'Waiting on Vendor For Info','',0);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (12,'Pending TC Approval','Waiting on Town Council Approval',0);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (13,'Cancelled by Requestor','Contract was Cancelled by Internal Contract Requestor',0);
INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`, `contract_status_desc`, `sort_order`) VALUES (14,'Subscription (No Signature-No Contract)','',0);
/*!40000 ALTER TABLE `contract_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `contract_types`
--

LOCK TABLES `contract_types` WRITE;
/*!40000 ALTER TABLE `contract_types` DISABLE KEYS */;
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (1,'Professional Services','Engineering, Surveying, architectural, QBS Required',1,1,'2026-02-03 16:07:12','2026-08-19 18:20:34',NULL,'templates/docx/1/Contract for PROFESSIONAL SERVICES - QBS.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (2,'General Services','Services Contracts other than QBS',0,1,'2026-02-03 16:07:12','2026-07-15 15:36:04',NULL,'templates/docx/2/Contract for Maintenance and Repair Services2.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (3,'Construction- Formal','Public works construction contracts',1,1,'2026-02-03 16:07:12','2026-02-13 15:05:25',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (4,'Construction- Informal','Public works construction contracts',0,1,'2026-02-03 16:07:12','2026-03-30 22:39:39',NULL,'storage/templates/4/Informal Construction_Based on EJCDC.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (5,'Purchase of Goods','Goods and supplies purchase with NO installation needed.',0,1,'2026-02-03 16:07:12','2026-08-14 18:25:30',NULL,'templates/docx/5/Contract for PURCHASE OF ASSETS -no install.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (6,'Purchase Order- Informal','Goods and supplies purchased via PO',0,0,'2026-02-03 16:07:12','2026-04-23 11:47:26',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (7,'Interlocal Agreement','Agreements between governmental entities',0,1,'2026-02-03 16:07:12','2026-04-30 15:12:55',NULL,'storage/templates/7/western-wake-ert-mutual-assistance-agreement_holly-springs-mxBMbNM6O0T51aj2.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (8,'Software / SaaS','Software licensing and subscriptions',0,1,'2026-02-03 16:07:12','2026-02-03 16:07:12',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (9,'Emergency Procurement','Emergency purchases under NC law',0,0,'2026-02-03 16:07:12','2026-08-12 17:29:05',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (10,'Development Agreement','Agreement with Developer',0,1,'2026-02-03 16:07:12','2026-06-30 21:13:44',NULL,'templates/docx/10/Windows saved_Dev_Agreement_Template.docx','templates/html/10/Development_20Agreement_20-_20PACT_20Merge_20Template.html',NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (11,'Master Services Agreement','Agreement without a specific SOW for on-call work',0,1,'2026-02-03 16:07:12','2026-08-11 14:41:53',NULL,'templates/docx/11/MSA_template_PACT.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (12,'Change Order','Change order to existing contract',0,1,'2026-02-03 16:07:12','2026-09-01 17:53:20',NULL,'templates/docx/12/Change Order Template.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (13,'P&R Entertainment Contract','Contract for Parks and Rec Entertainer',0,1,'2026-02-03 05:00:00','2026-03-24 23:01:42',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (14,'Test','Test with Merge Fields',0,0,'2026-02-08 00:42:54','2026-04-23 11:39:04',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (16,'Design Build','Base Design Build Contract Template',1,1,'2026-04-20 18:46:18','2026-08-07 01:59:17',NULL,'templates/docx/16/Design-Build Agreement.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (17,'Contract Modification (non construction)','to modify all non-construction service contracts.',0,1,'2026-04-29 13:05:04','2026-08-19 19:26:45',NULL,'templates/docx/17/Contract Amendment - Simple.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (18,'Contract Summary Form','Just a form that summarizes all the contract info',0,1,'2026-04-30 20:45:15','2026-04-30 20:45:22',NULL,'storage/templates/18/unified_contract_template.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (19,'Lease','Lease of property or goods where town is either lessor or lessee',0,1,'2026-06-04 21:20:55','2026-06-04 21:20:55',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (20,'CMAR- Long Version','Construction manager at Risk- LONG CONTRACT',1,1,'2026-06-25 13:03:48','2026-08-07 01:33:31',NULL,'templates/docx/20/CONSTRUCTION MANAGER AT RISK AGREEMENT.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (21,'Downtown Investment Grant','Investment grants pursuant to DDI/DIG Policy',0,1,'2026-06-29 15:15:46','2026-06-30 14:17:24',NULL,'templates/docx/21/DIGTest.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (22,'Joint Use Agreement','Agreement for Joint Use of a Facility with a public or private group',0,1,'2026-08-03 15:42:03','2026-08-03 15:42:03',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (23,'Purchase of Goods with Installation','Purchase of goods that require installation on Town Property.',0,1,'2026-08-07 15:56:47','2026-08-14 18:25:56',NULL,'templates/docx/23/Contract for PURCHASE OF ASSETS with Install.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (24,'P & R Instructor Contract','Contract for instructors at Parks & Rec',0,1,'2026-08-11 17:31:39','2026-08-11 17:31:39',NULL,NULL,NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (25,'Master Purchasing Agreement','This is an agreement to purchase goods at a fixed price, can be multi-year.',0,1,'2026-08-14 14:05:41','2026-08-14 14:06:00',NULL,'templates/docx/25/Master Purchasing Agreement Template2.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (26,'eVerify Affidavit','eVerify Affidavit with fields placed',0,1,'2026-08-19 18:34:41','2026-08-19 18:35:10',NULL,'templates/docx/26/eVerify Affidavit.docx',NULL,NULL);
INSERT INTO `contract_types` (`contract_type_id`, `contract_type`, `description`, `formal_bidding_required`, `is_active`, `created_at`, `updated_at`, `template_file`, `template_file_docx`, `template_file_html`, `contact_id`) VALUES (27,'E-Verify Affdavit','Blank E-Verify Affidavit',0,1,'2026-09-15 17:07:37','2026-09-15 17:07:45',NULL,'templates/docx/27/EXHIBIT C Blank E-Verify Affidavit.docx',NULL,NULL);
/*!40000 ALTER TABLE `contract_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payment_terms`
--

LOCK TABLES `payment_terms` WRITE;
/*!40000 ALTER TABLE `payment_terms` DISABLE KEYS */;
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (1,'Lump Sum with a Maximum','Fixed amount not to exceed a specified ceiling',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (2,'Lump Sum','Fixed price contract',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (3,'Unit Price','Payment based on quantities completed',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (4,'Cost Plus with a Maximum','Reimbursement of costs plus a fee',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (5,'Time and Materials','Payment for labor hours and materials used',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (6,'Guaranteed Maximum Price','Cost plus with a capped maximum',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (7,'Milestone Payments','Payments triggered by completion milestones',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (9,'Rent/Royalty','Town Receiving money from Vendor (Concessions)',1,0);
INSERT INTO `payment_terms` (`payment_terms_id`, `name`, `description`, `active`, `sort_order`) VALUES (10,'Reimbursement to town','Category describes where town gets money back',1,0);
/*!40000 ALTER TABLE `payment_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `procurement_methods`
--

LOCK TABLES `procurement_methods` WRITE;
/*!40000 ALTER TABLE `procurement_methods` DISABLE KEYS */;
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (1,'Competitive Bid (IFB)','Pursuant to NCGS 143-129',1,10);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (2,'Request for Proposals (RFP)',NULL,1,20);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (3,'Sole Source / Single Source','NCGS 143-129 (e)(6) Purchases of apparatus, supplies, materials, or equipment when: (i) performance or price competition for a product are not available; (ii) a needed product is available from only one source of supply; or (iii) standardization or compatibility is the overriding consideration. Notwithstanding any other provision of this section, the governing board of a political subdivision of the State shall approve the purchases listed in the preceding sentence prior to the award of the contract.\r\n\r\nIn the case of purchases by hospitals, in addition to the other exceptions in this subsection, the provisions of this Article shall not apply when: (i) a particular medical item or prosthetic appliance is needed; (ii) a particular product is ordered by an attending physician for his patients; (iii) additional products are needed to complete an ongoing job or task; (iv) products are purchased for \"over-the-counter\" resale; (v) a particular product is needed or desired for experimental, developmental, or research work; or (vi) equipment is already installed, connected, and in service under a lease or other agreement and the governing body of the hospital determines that the equipment should be purchased. The governing body of a hospital shall keep a record of all purchases made pursuant to this subdivision. These records are subject to public inspection.',1,30);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (4,'Emergency Purchase','NCGS 143-129 (e)(2) - Cases of special emergency involving the health and safety of the people or their property.',1,40);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (5,'Cooperative Agreement Purchase','Purchases or repair work involving a combination of installation labor and equipment acquisition made through a competitive bidding group purchasing program, which is a formally organized program that offers competitively obtained purchasing services at discount prices to two or more public agencies. \"Repair work\" is (i) limited to the repair of heating and cooling systems, (ii) may not exceed a total cost of two million dollars ($2,000,000) for installation labor or other related costs incidental to equipment acquisition, and (iii) is procured using a competitive bidding group purchasing program that is qualified to sell to the United States of America or any agency thereof.  NCGS 143-129 (e)  (3)',1,50);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (6,'Small / Informal Purchase (below threshold)','For the purchase of goods below $90,000',1,60);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (7,'Professional Services (QBS)','I compliance with with N.C.G.S. 64-31, the most qualified firm was selected by the town through qualification based selection.',1,70);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (8,'Service (non QBS)','General services not required to be bid.',1,80);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (9,'Not Required',NULL,1,90);
INSERT INTO `procurement_methods` (`procurement_method_id`, `short_desc`, `long_desc`, `active`, `sort_order`) VALUES (10,'Piggy Back Purchase','Waiver, by resolution of the Town Council, of bidding requirements for the purchase of apparatus, supplies, materials, or equipment from a company who has provided the same to a public entity under public bidding laws within the last 12 months.',1,0);
/*!40000 ALTER TABLE `procurement_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `town_locations`
--

LOCK TABLES `town_locations` WRITE;
/*!40000 ALTER TABLE `town_locations` DISABLE KEYS */;
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (1,'Town Hall','128 South Main St','Town Hall','Holly Springs','NC','27540',1,'2026-08-07 19:59:01','2026-08-07 19:59:01');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (2,'Police Department','750 Holly Springs Road','128 South Main Street','Holly Springs','NC','27540',1,'2026-08-07 19:59:45','2026-08-07 19:59:45');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (3,'Cultual Center','300 W. Ballentine St',NULL,'Holly Springs','NC','27540',1,'2026-08-07 20:00:13','2026-08-07 20:00:13');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (4,'W.E. Hunt Center','301 Stinson Street',NULL,'Holly Springs','NC','27540',1,'2026-08-07 20:00:40','2026-08-07 20:00:40');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (5,'Public Works','409 Innovate Way',NULL,'Holly Springs','NC','27540',1,'2026-08-07 20:02:07','2026-08-07 20:02:07');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (6,'North Main Athletic','101 Sportsmanship Way',NULL,'Holly Springs','NC','27540',1,'2026-08-07 20:03:12','2026-08-07 20:03:12');
INSERT INTO `town_locations` (`location_id`, `location_name`, `address_line1`, `address_line2`, `city`, `state_region`, `postal_code`, `is_active`, `created_at`, `updated_at`) VALUES (7,'Utley Creek Water Reclamation Facility','150 Treatment Plant Road',NULL,'Holly Springs','NC','27540',1,'2026-08-07 20:05:22','2026-08-07 20:05:22');
/*!40000 ALTER TABLE `town_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `company_types`
--

LOCK TABLES `company_types` WRITE;
/*!40000 ALTER TABLE `company_types` DISABLE KEYS */;
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (1,'LLC','Limited Liability Company',1);
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (2,'Corporation','C-Corp or S-Corp',1);
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (3,'Sole Proprietor','Individual / DBA',1);
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (4,'Partnership','General or Limited Partnership',1);
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (5,'Nonprofit','501(c)(3) or other nonprofit',1);
INSERT INTO `company_types` (`company_type_id`, `company_type`, `description`, `is_active`) VALUES (6,'Government','[Other Governmental Entity]',1);
/*!40000 ALTER TABLE `company_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `document_categories`
--

LOCK TABLES `document_categories` WRITE;
/*!40000 ALTER TABLE `document_categories` DISABLE KEYS */;
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (1,'revised_vendor','Revised by Vendor',1,1,10);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (2,'revised_internal','Revised Internally',1,1,20);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (3,'exhibit','Exhibit',1,1,30);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (4,'change_order','Change Order & Supporting Documents',1,1,40);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (5,'executed_contract','Executed Contract',0,1,50);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (6,'subscription_no_signature_no_contract','Subscription (No Signature-No Contract)',0,1,60);
INSERT INTO `document_categories` (`category_id`, `category_key`, `label`, `is_system`, `is_active`, `sort_order`) VALUES (7,'agenda_sheet_for_approval','Agenda Sheet for Approval',0,1,70);
/*!40000 ALTER TABLE `document_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bidding_compliance_event_types`
--

LOCK TABLES `bidding_compliance_event_types` WRITE;
/*!40000 ALTER TABLE `bidding_compliance_event_types` DISABLE KEYS */;
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (1,'Approval',1,10);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (2,'RFQ/RFP Published',1,20);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (3,'RFQ/RFP Received',1,30);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (4,'Selection Committee Decision',1,40);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (5,'3 Informal Quotes Received',1,50);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (6,'Documents Saved Here',1,60);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (7,'Documents Saved with Project Manager',1,70);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (8,'No Bidding Required',1,10);
INSERT INTO `bidding_compliance_event_types` (`event_type_id`, `label`, `active`, `sort_order`) VALUES (15,'Add Consortium Info',1,0);
/*!40000 ALTER TABLE `bidding_compliance_event_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-24  2:03:32

--
-- PACT installation reference data
-- Organization-specific personnel assignments intentionally omitted.
--

INSERT INTO `departments`
(`department_id`, `department_code`, `department_name`, `dept_initials`,
 `is_active`, `notes`, `department_head_id`,
 `assistant_town_manager_id`, `contract_admin_id`)
VALUES
(1,'ADMIN','Administration','AD',1,'',NULL,NULL,NULL),
(4,'PIO','Public Information / Communications','PIO',1,NULL,NULL,NULL,NULL),
(5,'FIN','Finance','FIN',1,NULL,NULL,NULL,NULL),
(6,'HR','Human Resources','HR',1,NULL,NULL,NULL,NULL),
(7,'LEGAL','Legal/Litigation','LE',1,NULL,NULL,NULL,NULL),
(8,'IT','Information Technology','IT',1,NULL,NULL,NULL,NULL),
(9,'UI','Utilities and Infrastructure','UI',1,NULL,NULL,NULL,NULL),
(10,'PW','Public Works','PW',1,NULL,NULL,NULL,NULL),
(12,'PARKS','Parks & Recreation','PR',1,NULL,NULL,NULL,NULL),
(13,'FIRE','Fire Department','FI',1,NULL,NULL,NULL,NULL),
(14,'POLICE','Police Department','PD',1,NULL,NULL,NULL,NULL),
(15,'DEV','Development Services','DS',1,NULL,NULL,NULL,NULL),
(16,'ECONDEV','Economic Development','ED',1,NULL,NULL,NULL,NULL),
(51,'CLK','Clerks Office','CL',1,NULL,NULL,NULL,NULL),
(52,'BUD','Budget, Innovation & Strategy','BS',1,NULL,NULL,NULL,NULL);

--
-- PACT currently uses company_id 3 as the owning organization.
--
INSERT INTO `companies`
(`company_id`, `name`, `type`, `company_type_id`, `is_active`)
VALUES
(3,'Town of Holly Springs','internal',6,1);

--
-- Portable system defaults.
-- Filesystem paths, SharePoint URLs, email addresses and stale template
-- defaults are intentionally not seeded.
--
INSERT INTO `system_settings`
(`setting_key`, `setting_value`, `description`, `updated_by`)
VALUES
(
 'contracts_generated_dir',
 'contracts',
 'Subfolder under storage_base_dir where generated contracts go',
 NULL
),
(
 'default_email_message',
 'Please find the attached document for the referenced contract.\n\nPlease review and let us know if you have any questions.\n\nRegards,\n{sender_name}',
 'Default email body used when emailing contract documents',
 NULL
);

--
-- Organization identity.
-- Person-specific assignments are configured after installation.
--
INSERT INTO `organization_settings`
(`id`, `org_name`, `org_type`, `website_url`, `logo_path`,
 `primary_contact_name`, `primary_contact_email`,
 `finance_director_name`, `mayor_or_exec_name`,
 `town_manager_person_id`, `town_clerk_person_id`,
 `town_attorney_person_id`, `finance_director_person_id`,
 `fiscal_year_start_month`)
VALUES
(
 1,
 'Town of Holly Springs',
 'town',
 'https://www.hollyspringsnc.gov/',
 'uploads/org_logo.png',
 NULL,
 NULL,
 NULL,
 NULL,
 NULL,
 NULL,
 NULL,
 NULL,
 7
);
