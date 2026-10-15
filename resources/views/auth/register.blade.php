<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Register</title></head>
<body>
    <h1>Student Registration</h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>Student number
            <input type="text" name="student_number" value="{{ old('student_number') }}" required>
        </label><br>
        <label>First name
            <input type="text" name="first_name" value="{{ old('first_name') }}" required>
        </label><br>
        <label>Last name
            <input type="text" name="last_name" value="{{ old('last_name') }}" required>
        </label><br>

        <label>Program
            <select name="program_id" required>
                <option value="">Select</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                        {{ $program->code }} - {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Year level
            <input type="number" name="year_level" min="1" max="6" value="{{ old('year_level') }}" required>
        </label><br>
        <label>Contact number
            <input type="text" name="contact_number" value="{{ old('contact_number') }}">
        </label><br>
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label><br>
        <label>Password
            <input type="password" name="password" required>
        </label><br>
        <label>Confirm password
            <input type="password" name="password_confirmation" required>
        </label><br>
        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="{{ route('login') }}">Login</a></p>
</body>
</html>