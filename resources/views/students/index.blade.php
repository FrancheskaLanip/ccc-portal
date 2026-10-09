@extends('layouts.app')
@section('title', 'Students')
@section('content')
    <h1>Students</h1>
    <p><a href="{{ route('students.create') }}">Add student</a></p>

    <table border="1" cellpadding="6">
        <tr>
            <th>Student no.</th><th>Name</th><th>Program</th>
            <th>Year</th><th>Status</th><th>Actions</th>
        </tr>
        @forelse ($students as $student)
            <tr>
                <td>{{ $student->student_number }}</td>
                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                <td>{{ $student->program->code }}</td>
                <td>{{ $student->year_level }}</td>
                <td>{{ $student->enrollment_status }}</td>
                <td>
                    <a href="{{ route('students.edit', $student) }}">Edit</a>
                    <form method="POST" action="{{ route('students.destroy', $student) }}" style="display:inline"
                          onsubmit="return confirm('Delete this student and their login?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No students yet.</td></tr>
        @endforelse
    </table>
@endsection