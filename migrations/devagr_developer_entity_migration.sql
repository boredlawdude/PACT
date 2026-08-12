-- devagr_developer_entity_migration.sql
-- Replaces applicant_id and property_owner_id (people FKs) in development_agreements
-- with free-text developer entity fields and a property_owner_name text field.

ALTER TABLE development_agreements
    -- Drop applicant FK + column
    DROP FOREIGN KEY fk_devagr_applicant,
    DROP KEY         fk_devagr_applicant,
    DROP COLUMN      applicant_id,
    -- Drop property_owner FK + column
    DROP FOREIGN KEY fk_devagr_property_owner,
    DROP KEY         fk_devagr_property_owner,
    DROP COLUMN      property_owner_id,
    -- Add free-text replacement fields
    ADD COLUMN property_owner_name              VARCHAR(200) DEFAULT NULL  AFTER attorney_id,
    ADD COLUMN developer_entity_name            VARCHAR(200) DEFAULT NULL  AFTER property_owner_name,
    ADD COLUMN developer_contact_name           VARCHAR(200) DEFAULT NULL  AFTER developer_entity_name,
    ADD COLUMN developer_address                VARCHAR(255) DEFAULT NULL  AFTER developer_contact_name,
    ADD COLUMN developer_phone                  VARCHAR(50)  DEFAULT NULL  AFTER developer_address,
    ADD COLUMN developer_email                  VARCHAR(200) DEFAULT NULL  AFTER developer_phone,
    ADD COLUMN developer_state_of_incorporation VARCHAR(100) DEFAULT NULL  AFTER developer_email,
    ADD COLUMN developer_entity_type            VARCHAR(50)  DEFAULT NULL  AFTER developer_state_of_incorporation;
