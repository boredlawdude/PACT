-- Add TOWN_ATTORNEY, TOWN_CLERK, FINANCE_DIRECTOR roles
-- Safe to run multiple times (INSERT IGNORE)
INSERT IGNORE INTO roles (role_key, role_name) VALUES
  ('TOWN_ATTORNEY',    'Town Attorney'),
  ('TOWN_CLERK',       'Town Clerk'),
  ('FINANCE_DIRECTOR', 'Finance Director');
