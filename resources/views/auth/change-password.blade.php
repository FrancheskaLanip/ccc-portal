<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Change Password</title></head>
<body>
    <h1>Change Password</h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf
        <label>Current password <input type="password" name="current_password" required></label><br>
        <label>New password <input type="password" name="password" required></label><br>
        <label>Confirm new password <input type="password" name="password_confirmation" required></label><br>
        <button type="submit">Save password</button>
    </form>

    <p><a href="{{ route('dashboard') }}">Back to dashboard</a></p>
</body>
</html>