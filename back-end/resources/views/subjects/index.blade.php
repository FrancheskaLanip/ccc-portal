{{-- SUBJECTS: list + add/edit form on one page. $editing is the subject being edited (or null). --}}
@extends('layouts.app')
@section('title', 'Subjects')
@section('content')
    <h1>Subjects</h1>

    <h2>{{ $editing ? 'Edit subject' : 'Add subject' }}</h2>
    <form method="POST" action="{{ $editing ? route('subjects.update', $editing) : route('subjects.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label>Code <input type="text" name="code" value="{{ old('code', $editing?->code) }}" required></label><br>
        <label>Title <input type="text" name="title" value="{{ old('title', $editing?->title) }}" required></label><br>
        <label>Units <input type="number" name="units" min="1" max="6" value="{{ old('units', $editing?->units ?? 3) }}" required></label><br>

        <p>Offered by these programs:</p>
        @foreach ($programs as $program)
            {{-- Checked if it was checked before an error (old) or is already linked ($linked) --}}
            <label>
                <input type="checkbox" name="programs[]" value="{{ $program->id }}"
                    {{ in_array($program->id, old('programs', $linked)) ? 'checked' : '' }}>
                {{ $program->code }}
            </label><br>
        @endforeach

        <button type="submit">Save</button>
        @if ($editing) <a href="{{ route('subjects.index') }}">Cancel</a> @endif
    </form>

    <hr>

    <table border="1" cellpadding="6">
        <tr><th>Code</th><th>Title</th><th>Units</th><th>Programs</th><th>Actions</th></tr>
        @forelse ($subjects as $subject)
            <tr>
                <td>{{ $subject->code }}</td>
                <td>{{ $subject->title }}</td>
                <td>{{ $subject->units }}</td>
                {{-- pluck('code') gets the program codes, join() puts commas between them --}}
                <td>{{ $subject->programs->pluck('code')->join(', ') ?: '-' }}</td>
                <td>
                    {{-- ?edit=ID reloads this page with the form filled in --}}
                    <a href="{{ route('subjects.index', ['edit' => $subject->id]) }}">Edit</a>
                    <form method="POST" action="{{ route('subjects.destroy', $subject) }}" style="display:inline"
                          onsubmit="return confirm('Delete this subject?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No subjects yet.</td></tr>
        @endforelse
    </table>
@endsection