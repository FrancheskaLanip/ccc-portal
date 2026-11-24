{{-- ANNOUNCEMENTS: everyone reads; admin/registrar also see the post/edit form. --}}
@extends('layouts.app')
@section('title', 'Announcements')
@section('content')
    <h1>Announcements</h1>

    @if (in_array(auth()->user()->role, ['admin', 'registrar']))
        <h2>{{ $editing ? 'Edit announcement' : 'Post an announcement' }}</h2>
        <form method="POST" action="{{ $editing ? route('announcements.update', $editing) : route('announcements.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <label>Title <input type="text" name="title" value="{{ old('title', $editing?->title) }}" required></label><br>
            <label>Message<br>
                <textarea name="body" rows="4" cols="50" required>{{ old('body', $editing?->body) }}</textarea>
            </label><br>
            <label>For
                <select name="audience">
                    @foreach (['all' => 'Everyone', 'student' => 'Students only', 'faculty' => 'Faculty only'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('audience', $editing?->audience ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label><br>
            <button type="submit">{{ $editing ? 'Save' : 'Post' }}</button>
            @if ($editing) <a href="{{ route('announcements.index') }}">Cancel</a> @endif
        </form>
        <hr>
    @endif

    @forelse ($announcements as $announcement)
        <h3>{{ $announcement->title }}</h3>
        <small>
            {{ $announcement->created_at->format('M d, Y') }}
            by {{ $announcement->poster?->name ?? 'Unknown' }}
            @if (in_array(auth()->user()->role, ['admin', 'registrar']))
                (for: {{ $announcement->audience }})
            @endif
        </small>
        {{-- nl2br(e(...)) keeps line breaks and still escapes any HTML --}}
        <p>{!! nl2br(e($announcement->body)) !!}</p>

        @if (in_array(auth()->user()->role, ['admin', 'registrar']))
            <a href="{{ route('announcements.index', ['edit' => $announcement->id]) }}">Edit</a>
            <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" style="display:inline"
                  onsubmit="return confirm('Delete this announcement?')">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        @endif
        <hr>
    @empty
        <p>No announcements yet.</p>
    @endforelse
@endsection