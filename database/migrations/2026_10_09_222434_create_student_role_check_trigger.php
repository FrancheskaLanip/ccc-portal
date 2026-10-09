<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
        CREATE OR REPLACE FUNCTION check_student_user_role() RETURNS trigger AS $$
        BEGIN
            IF (SELECT role FROM users WHERE id = NEW.user_id) IS DISTINCT FROM 'student' THEN
                RAISE EXCEPTION 'users.id % is not a student account', NEW.user_id;
            END IF;
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER students_user_role_check
        BEFORE INSERT OR UPDATE OF user_id ON students
        FOR EACH ROW EXECUTE FUNCTION check_student_user_role();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS students_user_role_check ON students');
        DB::unprepared('DROP FUNCTION IF EXISTS check_student_user_role()');
    }
};