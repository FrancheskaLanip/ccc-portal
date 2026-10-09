<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Register</title></head>
<body>
    <h1>Register</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>Name <input type="text" name="name" value="{{ old('name') }}" required></label><br>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
        <label>Password <input type="password" name="password" required></label><br>
        <label>Confirm password <input type="password" name="password_confirmation" required></label><br>
        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="{{ route('login') }}">Login</a></p>
</body>
</html>