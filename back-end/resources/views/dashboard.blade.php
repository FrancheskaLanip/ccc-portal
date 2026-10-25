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
    @endif

    {{-- Links for admin and registrar only --}}
    @if (in_array(auth()->user()->role, ['admin', 'registrar']))
        <p><a href="{{ route('staff') }}">Staff page</a></p>
        <p><a href="{{ route('programs.index') }}">Programs</a></p>
        <p><a href="{{ route('students.index') }}">Students</a></p>
    @endif

    {{-- Links for admin only --}}
    @if (auth()->user()->role === 'admin')
        <p><a href="{{ route('admin') }}">Admin page</a></p>
        <p><a href="{{ route('users.index') }}">Manage accounts</a></p>
    @endif

    {{-- Everyone can change their own password --}}
    <p><a href="{{ route('password.change') }}">Change password</a></p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>