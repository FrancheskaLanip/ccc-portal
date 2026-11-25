{{-- SUBJECTS: two pages in one file. /subjects = list, ?create=1 = add form, ?edit=ID = edit form. --}}
@extends('layouts.app')
@section('title', 'Subjects')
@section('content')
@if ($editing || request()->has('create'))

    {{-- PAGE 2: form only --}}
    <h1>{{ $editing ? 'Edit subject' : 'Add subject' }}</h1>
    <p><a href="{{ route('subjects.index') }}">&larr; Back to Subjects</a></p>

    <form method="POST" action="{{ $editing ? route('subjects.update', $editing) : route('subjects.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <label>Code <input type="text" name="code" value="{{ old('code', $editing?->code) }}" required></label><br>
        <label>Title <input type="text" name="title" value="{{ old('title', $editing?->title) }}" required></label><br>
        <label>Units <input type="number" name="units" min="1" max="6" value="{{ old('units', $editing?->units ?? 3) }}" required></label><br>

        <p>Offered by these programs:</p>
        @foreach ($programs as $program)
            <label>
                <input type="checkbox" name="programs[]" value="{{ $program->id }}"
                    {{ in_array($program->id, old('programs', $linked)) ? 'checked' : '' }}>
                {{ $program->code }}
            </label><br>
        @endforeach

        <button type="submit">Save</button>
        <a href="{{ route('subjects.index') }}">Cancel</a>
    </form>

@else

    {{-- PAGE 1: list only --}}
    <h1>Subjects</h1>
    <p><a href="{{ route('subjects.index', ['create' => 1]) }}">+ Add subject</a></p>

    <table border="1" cellpadding="6">
        <tr><th>Code</th><th>Title</th><th>Units</th><th>Programs</th><th>Actions</th></tr>
        @forelse ($subjects as $subject)
            <tr>
                <td>{{ $subject->code }}</td>
                <td>{{ $subject->title }}</td>
                <td>{{ $subject->units }}</td>
                <td>{{ $subject->programs->pluck('code')->join(', ') ?: '-' }}</td>
                <td>
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

@endif
@endsections