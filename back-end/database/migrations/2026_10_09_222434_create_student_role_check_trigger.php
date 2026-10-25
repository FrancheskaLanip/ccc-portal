<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// MIGRATION: creates a PostgreSQL trigger. The database itself refuses a student row
// whose account is not a student, even if the Laravel code is bypassed.
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        -- The function: look up the account's role and stop with an error if it is not 'student'
        CREATE OR REPLACE FUNCTION check_student_user_role() RETURNS trigger AS $$
        BEGIN
            IF (SELECT role FROM users WHERE id = NEW.user_id) IS DISTINCT FROM 'student' THEN
                RAISE EXCEPTION 'users.id % is not a student account', NEW.user_id;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        -- The trigger: run the function before every INSERT, or UPDATE of user_id, on students
        CREATE TRIGGER students_user_role_check
        BEFORE INSERT OR UPDATE OF user_id ON students
        FOR EACH ROW EXECUTE FUNCTION check_student_user_role();
        SQL);
    }

    // Undo: remove the trigger first, then the function it uses
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS students_user_role_check ON students');
        DB::unprepared('DROP FUNCTION IF EXISTS check_student_user_role()');
    }
};
