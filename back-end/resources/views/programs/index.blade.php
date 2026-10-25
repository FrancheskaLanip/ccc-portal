{{-- PROGRAMS LIST: uses the shared layout, then fills in the title and content. --}}
@extends('layouts.app')
@section('title', 'Programs')
@section('content')
    <h1>Programs</h1>
    <p><a href="{{ route('programs.create') }}">Add program</a></p>

    <table border="1" cellpadding="6">
        <tr><th>Code</th><th>Name</th><th>Students</th><th>Actions</th></tr>
        {{-- Loop over the programs sent by the controller. @empty runs when the list is empty --}}
        @forelse ($programs as $program)
            <tr>
                <td>{{ $program->code }}</td>
                <td>{{ $program->name }}</td>
                {{-- students_count comes from withCount('students') in the controller --}}
                <td>{{ $program->students_count }}</td>
                <td>
                    <a href="{{ route('programs.edit', $program) }}">Edit</a>
                    {{-- Delete is a form with a confirm popup. @method('DELETE') because browsers only send GET/POST --}}
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
