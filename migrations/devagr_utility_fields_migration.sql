-- Add utility / land-use fields to development_agreements
ALTER TABLE `development_agreements`
  ADD COLUMN `number_of_units`      int           DEFAULT NULL COMMENT 'Number of single-family homes / ERUs',
  ADD COLUMN `daily_flow_maximum`   int           DEFAULT NULL COMMENT 'Maximum daily flow in gallons per day',
  ADD COLUMN `allocation_elements`  text          COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'List of allocation elements',
  ADD COLUMN `parkland_dedication`  tinyint(1)    NOT NULL DEFAULT 0 COMMENT 'Parkland dedication required (boolean)',
  ADD COLUMN `transportation_tier`  varchar(20)   COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tier 1 | Tier 2 | Tier 3';
