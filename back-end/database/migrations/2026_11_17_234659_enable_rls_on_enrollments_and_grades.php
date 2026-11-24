<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// ROW-LEVEL SECURITY for enrollments and grades (+ one extra policy on students).
//   admin / registrar : everything
//   faculty           : only the enrollments and grades of THEIR OWN class sections (may update grades)
//   student           : only their own enrollments and grades (read only)
//   ccc_readonly      : read only
// Run with the owner connection: php artisan migrate:fresh --seed --database=pgsql_admin
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        -- Helper: the students.id of the logged-in student. SECURITY DEFINER lets it read "students"
        -- without going through that table's policies (this avoids an endless policy loop).
        CREATE OR REPLACE FUNCTION app_student_id() RETURNS bigint AS $$
            SELECT id FROM students
             WHERE user_id = NULLIF(current_setting('app.user_id', true), '')::bigint
        $$ LANGUAGE sql STABLE SECURITY DEFINER;

        ALTER TABLE enrollments ENABLE ROW LEVEL SECURITY;
        ALTER TABLE grades ENABLE ROW LEVEL SECURITY;

        -- ENROLLMENTS
        CREATE POLICY enrollments_staff_all ON enrollments FOR ALL
            USING (current_setting('app.role', true) IN ('admin', 'registrar'))
            WITH CHECK (current_setting('app.role', true) IN ('admin', 'registrar'));

        CREATE POLICY enrollments_student_own ON enrollments FOR SELECT
            USING (student_id = app_student_id());

        CREATE POLICY enrollments_faculty_select ON enrollments FOR SELECT
            USING (current_setting('app.role', true) = 'faculty'
                   AND class_section_id IN (SELECT id FROM class_sections
                                             WHERE faculty_id = NULLIF(current_setting('app.user_id', true), '')::bigint));

        -- GRADES
        CREATE POLICY grades_staff_all ON grades FOR ALL
            USING (current_setting('app.role', true) IN ('admin', 'registrar'))
            WITH CHECK (current_setting('app.role', true) IN ('admin', 'registrar'));

        CREATE POLICY grades_student_own ON grades FOR SELECT
            USING (enrollment_id IN (SELECT id FROM enrollments WHERE student_id = app_student_id()));

        CREATE POLICY grades_faculty_select ON grades FOR SELECT
            USING (current_setting('app.role', true) = 'faculty'
                   AND enrollment_id IN (SELECT e.id FROM enrollments e
                                           JOIN class_sections cs ON cs.id = e.class_section_id
                                          WHERE cs.faculty_id = NULLIF(current_setting('app.user_id', true), '')::bigint));

        CREATE POLICY grades_faculty_update ON grades FOR UPDATE
            USING (current_setting('app.role', true) = 'faculty'
                   AND enrollment_id IN (SELECT e.id FROM enrollments e
                                           JOIN class_sections cs ON cs.id = e.class_section_id
                                          WHERE cs.faculty_id = NULLIF(current_setting('app.user_id', true), '')::bigint))
            WITH CHECK (current_setting('app.role', true) = 'faculty'
                   AND enrollment_id IN (SELECT e.id FROM enrollments e
                                           JOIN class_sections cs ON cs.id = e.class_section_id
                                          WHERE cs.faculty_id = NULLIF(current_setting('app.user_id', true), '')::bigint));

        -- STUDENTS: faculty may read only the students enrolled in their own sections
        CREATE POLICY students_faculty_select ON students FOR SELECT
            USING (current_setting('app.role', true) = 'faculty'
                   AND id IN (SELECT student_id FROM enrollments));

        DO $$
        BEGIN
            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_readonly') THEN
                CREATE POLICY enrollments_readonly_select ON enrollments FOR SELECT TO ccc_readonly USING (true);
                CREATE POLICY grades_readonly_select ON grades FOR SELECT TO ccc_readonly USING (true);
                GRANT SELECT ON ALL TABLES IN SCHEMA public TO ccc_readonly;
            END IF;

            -- Rights on the new tables (the older GRANT only covered tables that existed back then)
            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_app') THEN
                GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO ccc_app;
                GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO ccc_app;
                GRANT EXECUTE ON FUNCTION app_student_id() TO ccc_app;
            END IF;
        END
        $$;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
        DROP POLICY IF EXISTS students_faculty_select ON students;
        DROP POLICY IF EXISTS grades_readonly_select ON grades;
        DROP POLICY IF EXISTS grades_faculty_update ON grades;
        DROP POLICY IF EXISTS grades_faculty_select ON grades;
        DROP POLICY IF EXISTS grades_student_own ON grades;
        DROP POLICY IF EXISTS grades_staff_all ON grades;
        DROP POLICY IF EXISTS enrollments_readonly_select ON enrollments;
        DROP POLICY IF EXISTS enrollments_faculty_select ON enrollments;
        DROP POLICY IF EXISTS enrollments_student_own ON enrollments;
        DROP POLICY IF EXISTS enrollments_staff_all ON enrollments;
        ALTER TABLE grades DISABLE ROW LEVEL SECURITY;
        ALTER TABLE enrollments DISABLE ROW LEVEL SECURITY;
        DROP FUNCTION IF EXISTS app_student_id();
        SQL);
    }
};
