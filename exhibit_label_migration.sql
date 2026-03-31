-- Add exhibit_label column to contract_documents
-- This stores the PDF stamp label per document (e.g. "Exhibit A", "Contract").
-- Leave NULL/blank to skip stamping for that document at merge time.

ALTER TABLE contract_documents
    ADD COLUMN exhibit_label VARCHAR(50) NULL DEFAULT NULL AFTER doc_type;
