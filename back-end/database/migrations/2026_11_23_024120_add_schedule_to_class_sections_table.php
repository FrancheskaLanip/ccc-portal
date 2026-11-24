<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: adds the schedule (day, time, room) to class_sections.
// A trigger stops two classes from using the same faculty or the same room at overlapping times.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->string('day')->nullable();           // Mon, Tue, ...
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room')->nullable();
        });

        DB::statement("ALTER TABLE class_sections ADD CONSTRAINT class_sections_day_check CHECK (day IS NULL OR day IN ('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'))");
        DB::statement('ALTER TABLE class_sections ADD CONSTRAINT class_sections_time_check CHECK (start_time IS NULL OR end_time IS NULL OR end_time > start_time)');

        DB::unprepared(<<<'SQL'
        -- Refuse a schedule that overlaps another class of the same faculty or in the same room
        CREATE OR REPLACE FUNCTION check_schedule_conflict() RETURNS trigger AS $$
        BEGIN
            IF NEW.day IS NULL OR NEW.start_time IS NULL OR NEW.end_time IS NULL THEN
                RETURN NEW;      -- no schedule yet, nothing to check
            END IF;

            -- Two times overlap when each one starts before the other one ends
            IF EXISTS (SELECT 1 FROM class_sections cs
                        WHERE cs.id IS DISTINCT FROM NEW.id
                          AND cs.school_year = NEW.school_year AND cs.semester = NEW.semester
                          AND cs.day = NEW.day AND cs.faculty_id = NEW.faculty_id
                          AND cs.start_time < NEW.end_time AND cs.end_time > NEW.start_time) THEN
                RAISE EXCEPTION 'Schedule conflict: this faculty already has a class at that time';
            END IF;

            IF NEW.room IS NOT NULL AND EXISTS (SELECT 1 FROM class_sections cs
                        WHERE cs.id IS DISTINCT FROM NEW.id
                          AND cs.school_year = NEW.school_year AND cs.semester = NEW.semester
                          AND cs.day = NEW.day AND cs.room = NEW.room
                          AND cs.start_time < NEW.end_time AND cs.end_time > NEW.start_time) THEN
                RAISE EXCEPTION 'Schedule conflict: room % is already used at that time', NEW.room;
            END IF;

            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;

        CREATE TRIGGER class_sections_schedule_check
        BEFORE INSERT OR UPDATE OF faculty_id, school_year, semester, day, start_time, end_time, room ON class_sections
        FOR EACH ROW EXECUTE FUNCTION check_schedule_conflict();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS class_sections_schedule_check ON class_sections');
        DB::unprepared('DROP FUNCTION IF EXISTS check_schedule_conflict()');

        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropColumn(['day', 'start_time', 'end_time', 'room']);
        });
    }
};