{{-- ADD PROGRAM: the fields are in _form.blade.php, shared with the edit page. --}}
@extends('layouts.app')
@section('title', 'Add Program')
@section('content')
    <h1>Add Program</h1>
    <form method="POST" action="{{ route('programs.store') }}">
        @include('programs._form')
    </form>
@endsection
