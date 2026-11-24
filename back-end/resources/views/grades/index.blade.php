{{-- MY CLASSES (faculty): own sections, and the class list with grade forms. --}}
@extends('layouts.app')
@section('title', 'My Classes')
@section('content')
<h1>My Classes</h1>

<table border="1" cellpadding="6">
    <tr>
        <th>Subject</th>
        <th>Section</th>
        <th>Term</th>
        <th>Schedule</th>
        <th>Students</th>
        <th>Action</th>
    </tr>
    @forelse ($sections as $item)
    <tr>
        <td>{{ $item->subject->code }} - {{ $item->subject->title }}</td>
        <td>{{ $item->section_name }}</td>
        <td>{{ $item->school_year }} {{ $item->semester }}</td>
        <td>{{ $item->schedule }}</td>
        <td>{{ $item->enrolled_count }}</td>
        <td><a href="{{ route('my-classes', ['section' => $item->id]) }}">Open class list</a></td>
    </tr>
    @empty
    <tr>
        <td colspan="6">You have no class sections.</td>
    </tr>
    @endforelse
</table>

{{-- The class list of the opened section --}}
@if ($section)
<h2>{{ $section->subject->code }} - Section {{ $section->section_name }} ({{ $section->school_year }} {{ $section->semester }})</h2>
<table border="1" cellpadding="6">
    <tr>
        <th>Student</th>
        <th>Status</th>
        <th>Grade (1.00 - 5.00)</th>
        <th>Mark INC</th>
        <th>Remarks</th>
        <th>Action</th>
    </tr>
    @forelse ($enrollments as $enrollment)
    <tr>
        <td>{{ $enrollment->student->student_number }} - {{ $enrollment->student->last_name }}, {{ $enrollment->student->first_name }}</td>
        <td>{{ $enrollment->status }}</td>
        @if ($enrollment->status === 'enrolled' && $enrollment->grade)
        {{-- An empty form with an id; the inputs below point to it with form="..." --}}
        <td>
            <form id="grade-{{ $enrollment->grade->id }}" method="POST" action="{{ route('grades.update', $enrollment->grade) }}">
                @csrf
                @method('PUT')
            </form>
            <input form="grade-{{ $enrollment->grade->id }}" type="number" name="grade" step="0.25" min="1" max="5" value="{{ $enrollment->grade->grade }}">
        </td>
        <td><input form="grade-{{ $enrollment->grade->id }}" type="checkbox" name="remarks" value="INC" {{ $enrollment->grade->remarks === 'INC' ? 'checked' : '' }}></td>
        <td>{{ $enrollment->grade->remarks ?? '-' }}</td>
        <td><button form="grade-{{ $enrollment->grade->id }}" type="submit">Save</button></td>
        @else
        <td colspan="2">-</td>
        <td>{{ $enrollment->grade?->remarks ?? '-' }}</td>
        <td>-</td>
        @endif
    </tr>
    @empty
    <tr>
        <td colspan="6">No students in this section.</td>
    </tr>
    @endforelse
</table>
@endif
@endsection