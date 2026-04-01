-- Add procurement compliance fields to contracts table
ALTER TABLE contracts
    ADD COLUMN procurement_method  VARCHAR(100) NULL DEFAULT NULL AFTER documents_path,
    ADD COLUMN bid_rfp_number      VARCHAR(100) NULL DEFAULT NULL AFTER procurement_method,
    ADD COLUMN bid_documents_path  VARCHAR(500) NULL DEFAULT NULL AFTER bid_rfp_number,
    ADD COLUMN procurement_notes   TEXT         NULL DEFAULT NULL AFTER bid_documents_path;
