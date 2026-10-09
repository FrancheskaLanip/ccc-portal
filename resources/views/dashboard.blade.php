<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
    <h1>Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

    @if (in_array(auth()->user()->role, ['admin', 'registrar']))
        <p><a href="{{ route('staff') }}">Staff page</a></p>
    @endif

    @if (auth()->user()->role === 'admin')
        <p><a href="{{ route('admin') }}">Admin page</a></p>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>