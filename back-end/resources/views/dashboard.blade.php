{{-- DASHBOARD: what each role sees after login. --}}
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Dashboard</h1>
    {{-- auth()->user() is the logged-in user --}}
    <p>Welcome, {{ auth()->user()->name }}</p>

    {{-- Message from the controller (example: "Password changed.") --}}
    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    {{-- Students see their own class and enrollment status ($student comes from DashboardController) --}}
    @if ($student)
        <p>Class: {{ $student->program->code }} {{ $student->year_level }}{{ $student->section ? '-' . $student->section : '' }}</p>
        <p>Status: <strong>{{ $student->enrollment_status }}</strong></p>

        @if ($student->enrollment_status === 'Enrolled')
            <p>You are officially enrolled.</p>
        @else
            <p>Wait for the registrar to officially enroll you.</p>
        @endif

        {{-- MY SUBJECTS: the student's enrollments with their schedule and grades --}}
        <h2>My Subjects</h2>
        <table border="1" cellpadding="6">
            <tr>
                <th>Subject</th><th>Units</th><th>Section</th><th>Term</th><th>Schedule</th>
                <th>Faculty</th><th>Status</th><th>Grade</th><th>Remarks</th>
            </tr>
            @forelse ($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->classSection->subject->code }} - {{ $enrollment->classSection->subject->title }}</td>
                    <td>{{ $enrollment->classSection->subject->units }}</td>
                    <td>{{ $enrollment->classSection->section_name }}</td>
                    <td>{{ $enrollment->classSection->school_year }} {{ $enrollment->classSection->semester }}</td>
                    {{-- schedule comes from getScheduleAttribute() in the ClassSection model --}}
                    <td>{{ $enrollment->classSection->schedule }}</td>
                    <td>{{ $enrollment->classSection->faculty->name }}</td>
                    <td>{{ $enrollment->status }}</td>
                    <td>{{ $enrollment->grade?->grade ?? '-' }}</td>
                    <td>{{ $enrollment->grade?->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">You are not enrolled in any subject yet.</td></tr>
            @endforelse
        </table>
    @endif

    {{-- Links for admin and registrar only --}}
    @if (in_array(auth()->user()->role, ['admin', 'registrar']))
        <p><a href="{{ route('staff') }}">Staff page</a></p>
        <p><a href="{{ route('programs.index') }}">Programs</a></p>
        <p><a href="{{ route('students.index') }}">Students</a></p>
        <p><a href="{{ route('subjects.index') }}">Subjects</a></p>
        <p><a href="{{ route('class-sections.index') }}">Class Sections</a></p>
        <p><a href="{{ route('enrollments.index') }}">Enrollments</a></p>
    @endif

    {{-- Link for faculty only --}}
    @if (auth()->user()->role === 'faculty')
        <p><a href="{{ route('my-classes') }}">My Classes</a></p>
    @endif

    {{-- Links for admin only --}}
    @if (auth()->user()->role === 'admin')
        <p><a href="{{ route('admin') }}">Admin page</a></p>
        <p><a href="{{ route('users.index') }}">Manage accounts</a></p>
    @endif

    {{-- Everyone can read announcements and change their own password --}}
    <p><a href="{{ route('announcements.index') }}">Announcements</a></p>
    <p><a href="{{ route('password.change') }}">Change password</a></p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>