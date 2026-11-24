<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MIGRATION: "announcements" posted by the registrar/admin.
// audience: all, student or faculty. Row-level security makes the database itself
// show each role only the announcements meant for it.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('audience')->default('all');             // all | student | faculty
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('created_at');                             // newest first
        });

        DB::statement("ALTER TABLE announcements ADD CONSTRAINT announcements_audience_check CHECK (audience IN ('all', 'student', 'faculty'))");

        DB::unprepared(<<<'SQL'
        ALTER TABLE announcements ENABLE ROW LEVEL SECURITY;

        -- Admin and registrar: read, post, edit, delete everything
        CREATE POLICY announcements_staff_all ON announcements FOR ALL
            USING (current_setting('app.role', true) IN ('admin', 'registrar'))
            WITH CHECK (current_setting('app.role', true) IN ('admin', 'registrar'));

        -- Everyone else: read only the announcements for "all" or for their own role
        CREATE POLICY announcements_audience_select ON announcements FOR SELECT
            USING (audience = 'all' OR audience = current_setting('app.role', true));

        DO $$
        BEGIN
            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_readonly') THEN
                CREATE POLICY announcements_readonly_select ON announcements FOR SELECT TO ccc_readonly USING (true);
                GRANT SELECT ON announcements TO ccc_readonly;
            END IF;

            IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'ccc_app') THEN
                GRANT SELECT, INSERT, UPDATE, DELETE ON announcements TO ccc_app;
                GRANT USAGE, SELECT ON SEQUENCE announcements_id_seq TO ccc_app;
            END IF;
        END
        $$;
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');   // dropping the table also drops its policies
    }
};