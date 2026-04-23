-- Add change_orders table (sub-table of contracts)
CREATE TABLE IF NOT EXISTS `change_orders` (
  `change_order_id`     int          NOT NULL AUTO_INCREMENT,
  `contract_id`         int          NOT NULL,
  `change_order_number` varchar(50)  COLLATE utf8mb4_unicode_ci NOT NULL,
  `co_justification`    text         COLLATE utf8mb4_unicode_ci,
  `co_amount`           decimal(15,2) DEFAULT NULL,
  `approval_date`       date          DEFAULT NULL,
  `created_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_order_id`),
  KEY `idx_co_contract_id` (`contract_id`),
  CONSTRAINT `fk_co_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`contract_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
