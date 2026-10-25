{{-- FORGOT PASSWORD: the user types their registered email. --}}
<!DOCTYPE html>
<html lang="en">
{{-- Page settings: UTF-8 text and the title shown on the browser tab --}}
<head><meta charset="UTF-8"><title>Forgot Password</title></head>
<body>
    {{-- Main page title --}}
    <h1>Forgot Password</h1>
    {{-- Short instruction for the user --}}
    <p>Enter your registered email. We will send new login credentials to it.</p>

    {{-- Green message from the controller --}}
    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    {{-- Red list of validation errors --}}
    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- The form is sent with POST to the password.email route --}}
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        {{-- Email box (type="email" makes the browser check the format) --}}
        <label>Registered email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label><br>
        {{-- Submit button --}}
        <button type="submit">Send credentials</button>
    </form>

    {{-- Link back to the login page --}}
    <p><a href="{{ route('login') }}">Back to login</a></p>
</body>
</html>
