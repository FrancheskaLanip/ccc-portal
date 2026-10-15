CREATE ROLE ccc_app LOGIN PASSWORD 'change_me_app';
CREATE ROLE ccc_readonly LOGIN PASSWORD 'change_me_readonly';

GRANT CONNECT ON DATABASE ccc_portal TO ccc_app, ccc_readonly;
GRANT USAGE ON SCHEMA public TO ccc_app, ccc_readonly;

GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ccc_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ccc_app;

GRANT SELECT ON ALL TABLES IN SCHEMA public TO ccc_readonly;