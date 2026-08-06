-- Migration: add doc_category to contract_intake_exhibits
-- Tracks which of the 3 intake upload boxes a file came from:
--   'sow_proposal'              = Scope of Work / Proposal
--   'certificate_of_insurance'  = Certificate of Insurance
--   'other'                     = Other supporting documents (up to 2)
-- Compatible with MySQL 5.7+
-- Note: if column already exists this will error - that's safe to ignore.
ALTER TABLE contract_intake_exhibits
    ADD COLUMN doc_category VARCHAR(30) NOT NULL DEFAULT 'other' AFTER mime_type;
