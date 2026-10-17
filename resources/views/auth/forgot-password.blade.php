<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Forgot Password</title></head>
<body>
    <h1>Forgot Password</h1>
    <p>Enter your registered email. We will send new login credentials to it.</p>

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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>Registered email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label><br>
        <button type="submit">Send credentials</button>
    </form>

    <p><a href="{{ route('login') }}">Back to login</a></p>
</body>
</html>