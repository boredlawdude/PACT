-- DocuSign signature integration migration
-- Adds envelope tracking columns to contract_documents
-- Run once: mysql -u {user} -p {dbname} < docusign_signature_migration.sql

ALTER TABLE contract_documents
    ADD COLUMN docusign_envelope_id  VARCHAR(100) DEFAULT NULL AFTER mime_type,
    ADD COLUMN docusign_status       VARCHAR(50)  DEFAULT NULL AFTER docusign_envelope_id,
    ADD COLUMN docusign_sent_at      TIMESTAMP    NULL DEFAULT NULL AFTER docusign_status,
    ADD COLUMN docusign_completed_at TIMESTAMP    NULL DEFAULT NULL AFTER docusign_sent_at;
