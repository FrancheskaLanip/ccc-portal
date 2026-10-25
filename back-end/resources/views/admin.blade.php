{{-- Admin page: the route has role:admin, so only admins can open it. --}}
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Admin</title></head>
<body>
    <h1>Admin area (admin only)</h1>
    <a href="{{ route('dashboard') }}">Back</a>
</body>
</html>
