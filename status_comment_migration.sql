ALTER TABLE contracts
    ADD COLUMN status_comment VARCHAR(40) DEFAULT NULL AFTER contract_status_id;

-- Increase status_comment to support longer notes
ALTER TABLE contracts
    MODIFY COLUMN status_comment VARCHAR(255) DEFAULT NULL;
