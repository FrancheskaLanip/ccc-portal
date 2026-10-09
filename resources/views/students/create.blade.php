@extends('layouts.app')
@section('title', 'Add Student')
@section('content')
    <h1>Add Student</h1>
    <form method="POST" action="{{ route('students.store') }}">
        @include('students._form')
    </form>
@endsection