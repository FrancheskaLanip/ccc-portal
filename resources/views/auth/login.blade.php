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
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
        <label>Password <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>

    @if ($portal === 'student')
        <p>No account? <a href="{{ route('register') }}">Register</a></p>
    @endif
</body>
</html>