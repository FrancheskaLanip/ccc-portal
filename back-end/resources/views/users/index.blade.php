{{-- USERS LIST (admin only): staff and faculty accounts. --}}
@extends('layouts.app')
@section('title', 'Accounts')
@section('content')
    <h1>Accounts</h1>
    <p><a href="{{ route('users.create') }}">Add account</a></p>

    <table border="1" cellpadding="6">
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
        {{-- Loop over the accounts sent by the controller. @empty runs when the list is empty --}}
        @forelse ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
                    {{-- An admin cannot delete their own account --}}
                    @if ($user->id === auth()->id())
                        You
                    @else
                        {{-- Delete is a form with a confirm popup --}}
                        <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline"
                              onsubmit="return confirm('Delete this account?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4">No accounts yet.</td></tr>
        @endforelse
    </table>
@endsection