{{-- RESET PASSWORD: the user types the 6-digit code from the email and a new password. --}}
<!DOCTYPE html>
<html lang="en">
{{-- Page settings: UTF-8 text and the title shown on the browser tab --}}
<head><meta charset="UTF-8"><title>Reset Password</title></head>
<body>
    {{-- Main page title --}}
    <h1>Reset Password</h1>
    {{-- Short instruction for the user --}}
    <p>Enter the code we emailed you and choose a new password.</p>
    {{-- Hint for a first-time user: the first email has credentials, not a code --}}
    <p>If the email you received has login credentials instead of a code, just log in with them.</p>

    {{-- Green message from the controller --}}
    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    {{-- Red list of validation errors (example: wrong or expired code) --}}
    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- The form is sent with POST to the password.reset.update route --}}
    <form method="POST" action="{{ route('password.reset.update') }}">
        @csrf
        {{-- $email comes from the controller, so the email is already filled in --}}
        <label>Registered email
            <input type="email" name="email" value="{{ old('email', $email) }}" required>
        </label><br>
        {{-- Exactly 6 digits --}}
        {{-- The 6-digit code from the email (numbers only on phones, max 6 characters) --}}
        <label>Code
            <input type="text" name="code" inputmode="numeric" maxlength="6" value="{{ old('code') }}" required>
        </label><br>
        {{-- The new password the user wants --}}
        <label>New password <input type="password" name="password" required></label><br>
        {{-- password_confirmation is checked by the "confirmed" validation rule --}}
        <label>Confirm new password <input type="password" name="password_confirmation" required></label><br>
        {{-- Submit button --}}
        <button type="submit">Change password</button>
    </form>

    {{-- Link to ask for a new code --}}
    <p><a href="{{ route('password.request') }}">Send me another email</a></p>
    {{-- Link back to the login page --}}
    <p><a href="{{ route('login') }}">Back to login</a></p>
</body>
</html>
