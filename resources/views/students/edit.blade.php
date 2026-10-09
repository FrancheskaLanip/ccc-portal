@extends('layouts.app')
@section('title', 'Edit Student')
@section('content')
    <h1>Edit Student</h1>
    <form method="POST" action="{{ route('students.update', $student) }}">
        @method('PUT')
        @include('students._form')
    </form>
@endsection