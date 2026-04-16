-- Make counterparty_company_id nullable to support Development Agreements
-- (which do not have a counterparty company)
ALTER TABLE contracts
  MODIFY COLUMN counterparty_company_id INT NULL DEFAULT NULL;
