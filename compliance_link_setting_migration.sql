-- Add compliance_info_link to system_settings
INSERT INTO system_settings (setting_key, setting_value, description)
VALUES (
    'compliance_info_link',
    '',
    'URL linked from the Procurement & Public Bidding Compliance heading on contract create/edit. Leave blank to show plain text.'
)
ON DUPLICATE KEY UPDATE description = VALUES(description);
