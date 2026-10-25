{{-- CHANGE PASSWORD: for a logged-in user. --}}
<!DOCTYPE html>
<html lang="en">
{{-- Page settings: UTF-8 text and the title shown on the browser tab --}}
<head><meta charset="UTF-8"><title>Change Password</title></head>
<body>
    {{-- Main page title --}}
    <h1>Change Password</h1>

    {{-- Red list of validation errors --}}
    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    {{-- The form is sent with POST to the password.change.update route --}}
    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf
        {{-- The old password must be correct (rule: current_password) --}}
        <label>Current password <input type="password" name="current_password" required></label><br>
        {{-- The new password the user wants --}}
        <label>New password <input type="password" name="password" required></label><br>
        {{-- password_confirmation must match password (rule: confirmed) --}}
        <label>Confirm new password <input type="password" name="password_confirmation" required></label><br>
        {{-- Submit button --}}
        <button type="submit">Save password</button>
    </form>

    {{-- Link back to the dashboard --}}
    <p><a href="{{ route('dashboard') }}">Back to dashboard</a></p>
</body>
</html>
