-- ROLES AND PRIVILEGES: two database roles with different permissions.
-- Run this once as the postgres superuser. Change the passwords first!

-- ccc_app is for the Laravel app, ccc_readonly is for viewing only
CREATE ROLE ccc_app LOGIN PASSWORD 'change_me_app';
CREATE ROLE ccc_readonly LOGIN PASSWORD 'change_me_readonly';

-- Both roles may connect to the database and see the public schema
GRANT CONNECT ON DATABASE ccc_portal TO ccc_app, ccc_readonly;
GRANT USAGE ON SCHEMA public TO ccc_app, ccc_readonly;

-- The app can read and change data (but cannot create or drop tables)
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ccc_app;
-- Sequences make the auto-number ids work when inserting
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ccc_app;

-- The read-only role can only SELECT
GRANT SELECT ON ALL TABLES IN SCHEMA public TO ccc_readonly;
