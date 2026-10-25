{{-- LAYOUT: the shared page frame. Other pages fill in the title and content below. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- @yield shows the page title; "CCC Portal" is the default --}}
    <title>@yield('title', 'CCC Portal')</title>
</head>
<body>
    {{-- Top menu: links use route names from routes/web.php --}}
    <nav>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        | <a href="{{ route('programs.index') }}">Programs</a>
        | <a href="{{ route('students.index') }}">Students</a>
        {{-- Logout is a POST form (not a link) and needs @csrf --}}
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </nav>
    <hr>

    {{-- Green message after a successful action (set with ->with('success', ...) in controllers) --}}
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

    {{-- The page content goes here --}}
    @yield('content')
</body>
</html>
