@extends('layouts.app')
@section('title', 'Edit Program')
@section('content')
    <h1>Edit Program</h1>
    <form method="POST" action="{{ route('programs.update', $program) }}">
        @method('PUT')
        @include('programs._form')
    </form>
@endsection