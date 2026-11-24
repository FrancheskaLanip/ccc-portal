{{-- ENROLLMENTS: enroll form + list of all enrollments. --}}
@extends('layouts.app')
@section('title', 'Enrollments')
@section('content')
    <h1>Enrollments</h1>

    <h2>Enroll a student</h2>
    <form method="POST" action="{{ route('enrollments.store') }}">
        @csrf
        <label>Student
            <select name="student_id" required>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                        {{ $student->student_number }} - {{ $student->last_name }}, {{ $student->first_name }}
                    </option>
                @endforeach
            </select>
        </label><br>

        <label>Class section
            <select name="class_section_id" required>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(old('class_section_id') == $section->id)>
                        {{ $section->subject->code }}-{{ $section->section_name }}
                        ({{ $section->school_year }} {{ $section->semester }})
                        {{ $section->enrolled_count }}/{{ $section->max_students }} seats
                    </option>
                @endforeach
            </select>
        </label><br>

        <button type="submit">Enroll</button>
    </form>

    <hr>

    <table border="1" cellpadding="6">
        <tr>
            <th>Student</th><th>Subject</th><th>Section</th><th>Term</th>
            <th>Faculty</th><th>Status</th><th>Grade</th><th>Remarks</th><th>Action</th>
        </tr>
        @forelse ($enrollments as $enrollment)
            <tr>
                <td>{{ $enrollment->student->student_number }} - {{ $enrollment->student->last_name }}</td>
                <td>{{ $enrollment->classSection->subject->code }}</td>
                <td>{{ $enrollment->classSection->section_name }}</td>
                <td>{{ $enrollment->classSection->school_year }} {{ $enrollment->classSection->semester }}</td>
                <td>{{ $enrollment->classSection->faculty->name }}</td>
                <td>{{ $enrollment->status }}</td>
                <td>{{ $enrollment->grade?->grade ?? '-' }}</td>
                <td>{{ $enrollment->grade?->remarks ?? '-' }}</td>
                <td>
                    {{-- One small form: sends the opposite status --}}
                    <form method="POST" action="{{ route('enrollments.update', $enrollment) }}" style="display:inline"
                          onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('PUT')
                        @if ($enrollment->status === 'enrolled')
                            <input type="hidden" name="status" value="dropped">
                            <button type="submit">Drop</button>
                        @else
                            <input type="hidden" name="status" value="enrolled">
                            <button type="submit">Re-enroll</button>
                        @endif
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">No enrollments yet.</td></tr>
        @endforelse
    </table>
@endsection