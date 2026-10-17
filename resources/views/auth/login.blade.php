<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login</title></head>
<body>
    <h1>Login</h1>

    <p>
        <a href="{{ route('login', ['portal' => 'student']) }}">Student</a> |
        <a href="{{ route('login', ['portal' => 'faculty']) }}">Faculty</a> |
        <a href="{{ route('login', ['portal' => 'staff']) }}">Staff</a>
    </p>

    <h2>{{ ucfirst($portal) }} login</h2>

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="portal" value="{{ $portal }}">

        @if ($portal === 'student')
            <label>Student number <input type="text" name="login" value="{{ old('login') }}" required></label><br>
        @else
            <label>Email <input type="email" name="login" value="{{ old('login') }}" required></label><br>
        @endif

        <label>Password <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>

    <p><a href="{{ route('password.request') }}">Forgot password?</a></p>
</body>
</html>