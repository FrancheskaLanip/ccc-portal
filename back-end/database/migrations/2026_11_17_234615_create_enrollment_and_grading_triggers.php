<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// MIGRATION: PostgreSQL triggers for the enrollment and grading modules.
// The database enforces these rules even if the Laravel code is bypassed.
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        -- 1) A class section can only be handled by a faculty account
        CREATE OR REPLACE FUNCTION check_section_faculty_role() RETURNS trigger AS $$
        BEGIN
            IF (SELECT role FROM users WHERE id = NEW.faculty_id) IS DISTINCT FROM 'faculty' THEN
                RAISE EXCEPTION 'users.id % is not a faculty account', NEW.faculty_id;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER class_sections_faculty_check
        BEFORE INSERT OR UPDATE OF faculty_id ON class_sections
        FOR EACH ROW EXECUTE FUNCTION check_section_faculty_role();

        -- 2) Refuse an enrollment when the section is already full
        CREATE OR REPLACE FUNCTION check_section_capacity() RETURNS trigger AS $$
        DECLARE
            taken integer;
            max_seats integer;
        BEGIN
            IF NEW.status = 'enrolled' THEN
                SELECT max_students INTO max_seats FROM class_sections WHERE id = NEW.class_section_id;
                SELECT count(*) INTO taken FROM enrollments
                 WHERE class_section_id = NEW.class_section_id
                   AND status = 'enrolled'
                   AND id IS DISTINCT FROM NEW.id;
                IF taken >= max_seats THEN
                    RAISE EXCEPTION 'Class section % is full (% seats)', NEW.class_section_id, max_seats;
                END IF;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER enrollments_capacity_check
        BEFORE INSERT OR UPDATE OF status, class_section_id ON enrollments
        FOR EACH ROW EXECUTE FUNCTION check_section_capacity();

        -- 3) Every new enrollment automatically gets an empty grade row
        CREATE OR REPLACE FUNCTION create_blank_grade() RETURNS trigger AS $$
        BEGIN
            INSERT INTO grades (enrollment_id, created_at, updated_at) VALUES (NEW.id, now(), now());
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER enrollments_create_grade
        AFTER INSERT ON enrollments
        FOR EACH ROW EXECUTE FUNCTION create_blank_grade();

        -- 4) Remarks follow the grade: 3.00 or better = Passed, worse = Failed.
        --    INC and Dropped are set by hand and are kept as they are.
        CREATE OR REPLACE FUNCTION set_grade_remarks() RETURNS trigger AS $$
        BEGIN
            IF NEW.remarks IN ('INC', 'Dropped') THEN
                RETURN NEW;
            ELSIF NEW.grade IS NULL THEN
                NEW.remarks := NULL;
            ELSIF NEW.grade <= 3.00 THEN
                NEW.remarks := 'Passed';
            ELSE
                NEW.remarks := 'Failed';
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER grades_set_remarks
        BEFORE INSERT OR UPDATE OF grade, remarks ON grades
        FOR EACH ROW EXECUTE FUNCTION set_grade_remarks();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
        DROP TRIGGER IF EXISTS grades_set_remarks ON grades;
        DROP TRIGGER IF EXISTS enrollments_create_grade ON enrollments;
        DROP TRIGGER IF EXISTS enrollments_capacity_check ON enrollments;
        DROP TRIGGER IF EXISTS class_sections_faculty_check ON class_sections;
        DROP FUNCTION IF EXISTS set_grade_remarks();
        DROP FUNCTION IF EXISTS create_blank_grade();
        DROP FUNCTION IF EXISTS check_section_capacity();
        DROP FUNCTION IF EXISTS check_section_faculty_role();
        SQL);
    }
};