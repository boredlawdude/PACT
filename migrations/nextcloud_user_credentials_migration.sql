-- Adds per-user Nextcloud credentials for WebDAV explorer.
-- Run this once on the database used by contracts_app.

ALTER TABLE people
  ADD COLUMN nextcloud_username VARCHAR(255) NULL AFTER email,
  ADD COLUMN nextcloud_password TEXT NULL AFTER nextcloud_username;
