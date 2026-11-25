{{-- ANNOUNCEMENTS: everyone sees the list. Staff also get ?create=1 (post) and ?edit=ID (edit) form pages. --}}
@extends('layouts.app')
@section('title', 'Announcements')
@section('content')
@php $isStaff = in_array(auth()->user()->role, ['admin', 'registrar']); @endphp

@if ($isStaff && ($editing || request()->has('create')))

    {{-- PAGE 2: form only --}}
    <h1>{{ $editing ? 'Edit announcement' : 'Post an announcement' }}</h1>
    <p><a href="{{ route('announcements.index') }}">&larr; Back to Announcements</a></p>

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
        <a href="{{ route('announcements.index') }}">Cancel</a>
    </form>

@else

    {{-- PAGE 1: list only --}}
    <h1>Announcements</h1>
    @if ($isStaff)
        <p><a href="{{ route('announcements.index', ['create' => 1]) }}">+ Post an announcement</a></p>
    @endif

    @forelse ($announcements as $announcement)
        <h3>{{ $announcement->title }}</h3>
        <small>
            {{ $announcement->created_at->format('M d, Y') }}
            by {{ $announcement->poster?->name ?? 'Unknown' }}
            @if ($isStaff) (for: {{ $announcement->audience }}) @endif
        </small>
        <p>{!! nl2br(e($announcement->body)) !!}</p>

        @if ($isStaff)
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

@endif
@endsection