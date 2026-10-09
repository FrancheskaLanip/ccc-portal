@csrf

@unless (isset($student))
    <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
    <label>Initial password <input type="password" name="password" required></label><br>
@endunless

<label>Student number
    <input type="text" name="student_number" value="{{ old('student_number', $student->student_number ?? '') }}" required>
</label><br>
<label>First name
    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" required>
</label><br>
<label>Last name
    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name ?? '') }}" required>
</label><br>

<label>Program
    <select name="program_id" required>
        <option value="">Select</option>
        @foreach ($programs as $program)
            <option value="{{ $program->id }}" @selected(old('program_id', $student->program_id ?? '') == $program->id)>
                {{ $program->code }}
            </option>
        @endforeach
    </select>
</label><br>

<label>Year level
    <input type="number" name="year_level" min="1" max="6" value="{{ old('year_level', $student->year_level ?? '') }}" required>
</label><br>
<label>Contact number
    <input type="text" name="contact_number" value="{{ old('contact_number', $student->contact_number ?? '') }}">
</label><br>

<label>Status
    <select name="enrollment_status">
        @foreach (['Not Enrolled', 'Enrolled'] as $status)
            <option value="{{ $status }}" @selected(old('enrollment_status', $student->enrollment_status ?? 'Not Enrolled') === $status)>
                {{ $status }}
            </option>
        @endforeach
    </select>
</label><br>

<button type="submit">Save</button>
<a href="{{ route('students.index') }}">Cancel</a>