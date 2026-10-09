<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login</title></head>
<body>
    <h1>Login</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
        <label>Password <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>

    <p>No account? <a href="{{ route('register') }}">Register</a></p>
</body>
</html>