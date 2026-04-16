-- devagr_submissions_developer_entity_migration.sql
-- Adds developer entity and property_owner_name columns to
-- development_agreement_submissions (public intake form).

ALTER TABLE development_agreement_submissions
    ADD COLUMN property_owner_name              VARCHAR(200) DEFAULT NULL  AFTER submitter_company,
    ADD COLUMN developer_entity_name            VARCHAR(200) DEFAULT NULL  AFTER property_owner_name,
    ADD COLUMN developer_contact_name           VARCHAR(200) DEFAULT NULL  AFTER developer_entity_name,
    ADD COLUMN developer_address                VARCHAR(255) DEFAULT NULL  AFTER developer_contact_name,
    ADD COLUMN developer_phone                  VARCHAR(50)  DEFAULT NULL  AFTER developer_address,
    ADD COLUMN developer_email                  VARCHAR(200) DEFAULT NULL  AFTER developer_phone,
    ADD COLUMN developer_state_of_incorporation VARCHAR(100) DEFAULT NULL  AFTER developer_email,
    ADD COLUMN developer_entity_type            VARCHAR(50)  DEFAULT NULL  AFTER developer_state_of_incorporation;
