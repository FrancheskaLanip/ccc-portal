<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\ClassSection;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// SEEDER: fills an empty database with test data (php artisan migrate:fresh --seed).
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // forceCreate skips mass-assignment protection, so we can set the role here.
        // credentials_sent_at is also set on every test account: their password is already
        // known, so the first "Forgot password" sends a 6-digit code instead of new credentials.
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@ccc.test',
            'password' => Hash::make('password'),   // test password is "password"
            'role' => 'admin',
            'credentials_sent_at' => now(),
        ]);

        $registrar = User::forceCreate([
            'name' => 'Registrar',
            'email' => 'registrar@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'registrar',
            'credentials_sent_at' => now(),
        ]);

        $faculty = User::forceCreate([
            'name' => 'Faculty',
            'email' => 'faculty@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'credentials_sent_at' => now(),
        ]);

        // A second faculty, to show that each faculty only sees their own sections
        $faculty2 = User::forceCreate([
            'name' => 'Faculty Two',
            'email' => 'faculty2@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'faculty',
            'credentials_sent_at' => now(),
        ]);

        // Two sample programs
        $bspa = Program::create([
            'code' => 'BSPA',
            'name' => 'Bachelor of Science in Public Administration',
        ]);

        $bsm = Program::create([
            'code' => 'BSM',
            'name' => 'Bachelor of Science in Midwifery',
        ]);

        // Test student (logs in with the student number)
        $studentUser = User::forceCreate([
            'name' => 'Juan Dela Cruz',
            'email' => 'student@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'credentials_sent_at' => now(),
        ]);

        // Create the student record linked to this account (user_id is filled in automatically)
        $juan = $studentUser->student()->create([
            'program_id' => $bspa->id,
            'student_number' => 'CCC-2026-0001',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'year_level' => 1,
            'section' => 'A',
            'enrollment_status' => 'Enrolled',
        ]);

        // A second student (BSM)
        $mariaUser = User::forceCreate([
            'name' => 'Maria Santos',
            'email' => 'student2@ccc.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'credentials_sent_at' => now(),
        ]);

        $maria = $mariaUser->student()->create([
            'program_id' => $bsm->id,
            'student_number' => 'CCC-2026-0002',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'year_level' => 1,
            'section' => 'A',
            'enrollment_status' => 'Enrolled',
        ]);

        // Subjects: GE101, GE102 and PE101 are shared by both programs, the others belong to one program
        $subjects = [];
        foreach ([
            ['GE101', 'Understanding the Self', 3],
            ['GE102', 'Purposive Communication', 3],
            ['PE101', 'Physical Fitness and Wellness', 2],
            ['PA101', 'Introduction to Public Administration', 3],
            ['PA102', 'Public Policy Analysis', 3],
            ['MW101', 'Fundamentals of Midwifery', 4],
        ] as [$code, $title, $units]) {
            $subjects[$code] = Subject::create(['code' => $code, 'title' => $title, 'units' => $units]);
        }

        // Link subjects to programs (pivot table program_subject)
        $bspa->subjects()->attach([
            $subjects['GE101']->id => ['year_level' => 1],
            $subjects['GE102']->id => ['year_level' => 1],
            $subjects['PE101']->id => ['year_level' => 1],
            $subjects['PA101']->id => ['year_level' => 1],
            $subjects['PA102']->id => ['year_level' => 1],
        ]);
        $bsm->subjects()->attach([
            $subjects['GE101']->id => ['year_level' => 1],
            $subjects['GE102']->id => ['year_level' => 1],
            $subjects['PE101']->id => ['year_level' => 1],
            $subjects['MW101']->id => ['year_level' => 1],
        ]);

        // Class sections for 1st semester, with a schedule (PA102 is handled by the second faculty)
        $sections = [];
        foreach ([
            // code => [faculty, day, start, end, room]
            'GE101' => [$faculty, 'Mon', '08:00', '09:30', '101'],
            'GE102' => [$faculty, 'Mon', '09:30', '11:00', '101'],
            'PA101' => [$faculty, 'Tue', '08:00', '09:30', '102'],
            'MW101' => [$faculty, 'Wed', '08:00', '10:00', '201'],
            'PA102' => [$faculty2, 'Mon', '08:00', '09:30', '102'],
        ] as $code => [$teacher, $day, $start, $end, $room]) {
            $sections[$code] = ClassSection::create([
                'subject_id' => $subjects[$code]->id,
                'faculty_id' => $teacher->id,
                'section_name' => 'A',
                'school_year' => '2026-2027',
                'semester' => '1st',
                'max_students' => 40,
                'day' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'room' => $room,
            ]);
        }

        // Enroll the students (a database trigger creates an empty grade row for each enrollment)
        $enroll = function (Student $student, string $code) use ($sections) {
            return Enrollment::create([
                'student_id' => $student->id,
                'class_section_id' => $sections[$code]->id,
            ]);
        };

        $juanGe101 = $enroll($juan, 'GE101');
        $juanGe102 = $enroll($juan, 'GE102');
        $juanPa101 = $enroll($juan, 'PA101');
        $enroll($juan, 'PA102');
        $mariaGe101 = $enroll($maria, 'GE101');
        $enroll($maria, 'MW101');

        // Sample grades (a database trigger fills in the remarks: Passed / Failed)
        $juanGe101->grade->update(['grade' => 1.75, 'encoded_by' => $faculty->id, 'encoded_at' => now()]);
        $juanGe102->grade->update(['grade' => 2.25, 'encoded_by' => $faculty->id, 'encoded_at' => now()]);
        $mariaGe101->grade->update(['grade' => 5.00, 'encoded_by' => $faculty->id, 'encoded_at' => now()]);
        // INC is set by hand
        $juanPa101->grade->update(['remarks' => 'INC', 'encoded_by' => $faculty->id, 'encoded_at' => now()]);

        // Sample announcements (the database shows each role only the ones meant for it)
        foreach ([
            ['Welcome to the CCC Portal', 'The portal is now open for the 1st semester.', 'all'],
            ['Enrollment reminder', 'Students: check My Subjects on your dashboard.', 'student'],
            ['Grade encoding', 'Faculty: please encode grades under My Classes.', 'faculty'],
        ] as [$title, $body, $audience]) {
            Announcement::create(['title' => $title, 'body' => $body, 'audience' => $audience, 'posted_by' => $registrar->id]);
        }
    }
}