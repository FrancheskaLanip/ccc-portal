{{-- Shared form fields for add and edit student. $student exists only when editing. --}}
@csrf

{{-- The email field shows only when ADDING a student (the account email) --}}
@unless (isset($student))
    <label>Email (credentials will be sent here)
        <input type="email" name="email" value="{{ old('email') }}" required>
    </label><br>
@endunless

{{-- old() keeps typed text after an error; $student->... fills the field when editing; '' when adding --}}
<label>Student number
    <input type="text" name="student_number" value="{{ old('student_number', $student->student_number ?? '') }}" required>
</label><br>
<label>First name
    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" required>
</label><br>
<label>Last name
    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name ?? '') }}" required>
</label><br>

{{-- Dropdown filled from the programs table; @selected marks the saved program --}}
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
<label>Section
    <input type="text" name="section" maxlength="10" value="{{ old('section', $student->section ?? '') }}" placeholder="A">
</label><br>
<label>Contact number
    <input type="text" name="contact_number" value="{{ old('contact_number', $student->contact_number ?? '') }}">
</label><br>

{{-- Status dropdown: new students start as "Not Enrolled" --}}
<label>Status
    <select name="enrollment_status">
        @foreach (['Not Enrolled', 'Pending', 'Enrolled'] as $status)
            <option value="{{ $status }}" @selected(old('enrollment_status', $student->enrollment_status ?? 'Not Enrolled') === $status)>
                {{ $status }}
            </option>
        @endforeach
    </select>
</label><br>

<button type="submit">Save</button>
<a href="{{ route('students.index') }}">Cancel</a>
