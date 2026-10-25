{{-- STUDENTS LIST: uses the shared layout, then fills in the title and content. --}}
@extends('layouts.app')
@section('title', 'Students')
@section('content')
    <h1>Students</h1>
    <p><a href="{{ route('students.create') }}">Add student</a></p>

    <table border="1" cellpadding="6">
        <tr>
            <th>Student no.</th>
            <th>Name</th>
            <th>Class</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        {{-- Loop over the students sent by the controller. @empty runs when the list is empty --}}
        @forelse ($students as $student)
            <tr>
                <td>{{ $student->student_number }}</td>
                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                {{-- Example: BSPA 1-A (program code, year level, section) --}}
                <td>{{ $student->program->code }} {{ $student->year_level }}{{ $student->section ? '-' . $student->section : '' }}</td>
                <td>{{ $student->enrollment_status }}</td>
                <td>
                    <a href="{{ route('students.edit', $student) }}">Edit</a>

                    {{-- The enroll button shows only for students who are "Pending" --}}
                    @if ($student->enrollment_status === 'Pending')
                        <form method="POST" action="{{ route('students.enroll', $student) }}" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit">Confirm enrollment</button>
                        </form>
                    @endif

                    {{-- Delete is a form with a confirm popup --}}
                    <form method="POST" action="{{ route('students.destroy', $student) }}" style="display:inline"
                          onsubmit="return confirm('Delete this student and their login?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No students yet.</td>
            </tr>
        @endforelse
    </table>
@endsection
