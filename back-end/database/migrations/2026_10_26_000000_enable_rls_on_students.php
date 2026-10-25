<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// ROW-LEVEL SECURITY on "students".
// Run this with the owner connection: php artisan migrate:fresh --seed --database=pgsql_admin
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        ALTER TABLE students ENABLE ROW LEVEL SECURITY;

        -- Admin and registrar: full access to every student row
        CREATE POLICY students_staff_all ON students
            FOR ALL
            USING (current_setting('app.role', true) IN ('admin', 'registrar'))
            WITH CHECK (current_setting('app.role', true) IN ('admin', 'registrar'));

        -- "system" context (login and forgot password lookups): read only
        CREATE POLICY students_system_read ON students
            FOR SELECT
            USING (current_setting('app.role', true) = 'system');

        -- A student can read only their own row
        CREATE POLICY students_own_row ON students
            FOR SELECT
            USING (user_id = NULLIF(current_setting('app.user_id', true), '')::bigint);

        DO $$
        BEGIN
            -- The read-only database role may read every row (for reports and checking)
            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_readonly') THEN
                CREATE POLICY students_readonly_select ON students FOR SELECT TO ccc_readonly USING (true);
                GRANT SELECT ON ALL TABLES IN SCHEMA public TO ccc_readonly;
            END IF;

            -- The app role needs rights on every table, including the ones created by newer migrations
            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_app') THEN
                GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ccc_app;
                GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ccc_app;
            END IF;
        END
        $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
        DROP POLICY IF EXISTS students_readonly_select ON students;
        DROP POLICY IF EXISTS students_own_row ON students;
        DROP POLICY IF EXISTS students_system_read ON students;
        DROP POLICY IF EXISTS students_staff_all ON students;
        ALTER TABLE students DISABLE ROW LEVEL SECURITY;
        SQL);
    }
};
