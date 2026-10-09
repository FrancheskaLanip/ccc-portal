@extends('layouts.app')
@section('title', 'Programs')
@section('content')
    <h1>Programs</h1>
    <p><a href="{{ route('programs.create') }}">Add program</a></p>

    <table border="1" cellpadding="6">
        <tr><th>Code</th><th>Name</th><th>Students</th><th>Actions</th></tr>
        @forelse ($programs as $program)
            <tr>
                <td>{{ $program->code }}</td>
                <td>{{ $program->name }}</td>
                <td>{{ $program->students_count }}</td>
                <td>
                    <a href="{{ route('programs.edit', $program) }}">Edit</a>
                    <form method="POST" action="{{ route('programs.destroy', $program) }}" style="display:inline"
                          onsubmit="return confirm('Delete this program?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No programs yet.</td></tr>
        @endforelse
    </table>
@endsection