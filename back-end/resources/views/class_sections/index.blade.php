{{-- CLASS SECTIONS: list + add/edit form on one page. $editing is the section being edited (or null). --}}
@extends('layouts.app')
@section('title', 'Class Sections')
@section('content')
    <h1>Class Sections</h1>

    <h2>{{ $editing ? 'Edit class section' : 'Add class section' }}</h2>
    <form method="POST" action="{{ $editing ? route('class-sections.update', $editing) : route('class-sections.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label>Subject
            <select name="subject_id" required>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(old('subject_id', $editing?->subject_id) == $subject->id)>
                        {{ $subject->code }} - {{ $subject->title }}
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Faculty
            <select name="faculty_id" required>
                @foreach ($facultyList as $faculty)
                    <option value="{{ $faculty->id }}" @selected(old('faculty_id', $editing?->faculty_id) == $faculty->id)>
                        {{ $faculty->name }}
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Section name <input type="text" name="section_name" value="{{ old('section_name', $editing?->section_name) }}" required></label><br>
        <label>School year <input type="text" name="school_year" placeholder="2026-2027" value="{{ old('school_year', $editing?->school_year ?? '2026-2027') }}" required></label><br>

        <label>Semester
            <select name="semester" required>
                @foreach (['1st', '2nd', 'Summer'] as $sem)
                    <option value="{{ $sem }}" @selected(old('semester', $editing?->semester ?? '1st') == $sem)>{{ $sem }}</option>
                @endforeach
            </select>
        </label><br>

        <label>Max students <input type="number" name="max_students" min="1" max="200" value="{{ old('max_students', $editing?->max_students ?? 40) }}" required></label><br>

        {{-- Schedule (optional) --}}
        <label>Day
            <select name="day">
                <option value="">-</option>
                @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d)
                    <option value="{{ $d }}" @selected(old('day', $editing?->day) === $d)>{{ $d }}</option>
                @endforeach
            </select>
        </label><br>
        {{-- substr(..., 0, 5) turns 08:00:00 into 08:00 --}}
        <label>Start time <input type="time" name="start_time" value="{{ old('start_time', $editing?->start_time ? substr($editing->start_time, 0, 5) : '') }}"></label><br>
        <label>End time <input type="time" name="end_time" value="{{ old('end_time', $editing?->end_time ? substr($editing->end_time, 0, 5) : '') }}"></label><br>
        <label>Room <input type="text" name="room" value="{{ old('room', $editing?->room) }}"></label><br>

        <button type="submit">Save</button>
        @if ($editing) <a href="{{ route('class-sections.index') }}">Cancel</a> @endif
    </form>

    <hr>

    <table border="1" cellpadding="6">
        <tr><th>Subject</th><th>Section</th><th>School year</th><th>Semester</th><th>Schedule</th><th>Faculty</th><th>Seats</th><th>Actions</th></tr>
        @forelse ($sections as $section)
            <tr>
                <td>{{ $section->subject->code }} - {{ $section->subject->title }}</td>
                <td>{{ $section->section_name }}</td>
                <td>{{ $section->school_year }}</td>
                <td>{{ $section->semester }}</td>
                {{-- schedule comes from getScheduleAttribute() in the ClassSection model --}}
                <td>{{ $section->schedule }}</td>
                <td>{{ $section->faculty->name }}</td>
                {{-- enrolled_count comes from withCount() in the controller --}}
                <td>{{ $section->enrolled_count }} / {{ $section->max_students }}</td>
                <td>
                    <a href="{{ route('class-sections.index', ['edit' => $section->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('class-sections.destroy', $section) }}" style="display:inline"
                          onsubmit="return confirm('Delete this class section?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="8">No class sections yet.</td></tr>
        @endforelse
    </table>
@endsection