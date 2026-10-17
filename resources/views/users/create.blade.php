@extends('layouts.app')
@section('title', 'Add Account')
@section('content')
    <h1>Add Account</h1>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <label>Name <input type="text" name="name" value="{{ old('name') }}" required></label><br>
        <label>Email (credentials will be sent here)
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label><br>
        <label>Role
            <select name="role" required>
                @foreach (['faculty', 'registrar', 'admin'] as $role)
                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                @endforeach
            </select>
        </label><br>
        <button type="submit">Save</button>
        <a href="{{ route('users.index') }}">Cancel</a>
    </form>
@endsection