-- Add bidding_compliance_event_types lookup table (admin-manageable "Event" dropdown
-- for the Bidding Compliance Log). Existing bidding_compliance.event_type stays a
-- free-text varchar column (no FK) so historical records are unaffected by edits/
-- deletes made here.
CREATE TABLE IF NOT EXISTS `bidding_compliance_event_types` (
  `event_type_id` int NOT NULL AUTO_INCREMENT,
  `label`         varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active`        tinyint(1) NOT NULL DEFAULT '1',
  `sort_order`    int NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_type_id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `bidding_compliance_event_types` (`label`, `sort_order`) VALUES
  ('No Bidding Required', 10),
  ('RFQ/RFP Published', 20),
  ('RFQ/RFP Received', 30),
  ('Selection Committee Decision', 40),
  ('3 Informal Quotes Received', 50),
  ('Documents Saved Here', 60),
  ('Documents Saved with Project Manager', 70);
