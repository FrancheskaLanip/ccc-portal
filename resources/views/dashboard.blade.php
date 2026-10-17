<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }}</p>

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if (auth()->user()->role === 'student' && auth()->user()->student)
        @php($student = auth()->user()->student)
        <p>Class: {{ $student->program->code }} {{ $student->year_level }}{{ $student->section ? '-' . $student->section : '' }}</p>
        <p>Status: <strong>{{ $student->enrollment_status }}</strong></p>

        @if ($student->enrollment_status === 'Enrolled')
            <p>You are officially enrolled.</p>
        @else
            <p>Wait for the registrar to officially enroll you.</p>
        @endif
    @endif

    @if (in_array(auth()->user()->role, ['admin', 'registrar']))
        <p><a href="{{ route('staff') }}">Staff page</a></p>
        <p><a href="{{ route('programs.index') }}">Programs</a></p>
        <p><a href="{{ route('students.index') }}">Students</a></p>
    @endif

    @if (auth()->user()->role === 'admin')
        <p><a href="{{ route('admin') }}">Admin page</a></p>
        <p><a href="{{ route('users.index') }}">Manage accounts</a></p>
    @endif

    <p><a href="{{ route('password.change') }}">Change password</a></p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>