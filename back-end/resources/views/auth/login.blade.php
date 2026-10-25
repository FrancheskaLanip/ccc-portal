{{-- LOGIN PAGE: three login options (Student, Faculty, Staff). --}}
<!DOCTYPE html>
<html lang="en">
{{-- Page settings: UTF-8 text and the title shown on the browser tab --}}
<head><meta charset="UTF-8"><title>Login</title></head>
<body>
    {{-- Main page title --}}
    <h1>Login</h1>

    {{-- Switch between the login options (the controller reads ?portal=...) --}}
    <p>
        <a href="{{ route('login', ['portal' => 'student']) }}">Student</a> |
        <a href="{{ route('login', ['portal' => 'faculty']) }}">Faculty</a> |
        <a href="{{ route('login', ['portal' => 'staff']) }}">Staff</a>
    </p>

    {{-- Shows which login option is open (ucfirst makes the first letter a capital) --}}
    <h2>{{ ucfirst($portal) }} login</h2>

    {{-- Green message (example: "Password changed. You can log in now.") --}}
    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    {{-- Red list of errors (example: "Invalid login or password.") --}}
    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
        {{-- @csrf adds a hidden token that protects the form against fake requests --}}
        @csrf
        {{-- Tell the controller which login option was used --}}
        {{-- Hidden field: sends the chosen login option to the controller --}}
        <input type="hidden" name="portal" value="{{ $portal }}">

        {{-- Students type their student number, others type their email --}}
        @if ($portal === 'student')
            {{-- Student login: the student number --}}
            <label>Student number <input type="text" name="login" value="{{ old('login') }}" required></label><br>
        @else
            {{-- Faculty and staff login: the email --}}
            <label>Email <input type="email" name="login" value="{{ old('login') }}" required></label><br>
        @endif

        {{-- old('login') keeps what the user typed after a failed login --}}
        {{-- Password box (the typed characters are hidden) --}}
        <label>Password <input type="password" name="password" required></label><br>
        {{-- Submit button: sends the form to the controller with POST --}}
        <button type="submit">Login</button>
    </form>

    {{-- Link to the Forgot password page --}}
    <p><a href="{{ route('password.request') }}">Forgot password?</a></p>
</body>
</html>
