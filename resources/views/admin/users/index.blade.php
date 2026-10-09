@extends('layouts.app')

@section('content')

<h2 class="mb-4">Manage Users</h2>

<div class="card">

    <div class="card-body">

        <form method="GET" class="row mb-3">

            <div class="col-md-10">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search users..."
                       value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-success w-100">
                    Search
                </button>
            </div>

        </form>

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered</th>
                </tr>
            </thead>

            <tbody>

            @forelse($users as $user)

                <tr>

                    <td>{{ $user->name }}</td>

                    <td>{{ $user->email }}</td>

                    <td>
                        <span class="badge bg-primary">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>

                    <td>{{ $user->created_at->format('d M Y') }}</td>

                </tr>

            @empty

                <tr>
                    <td colspan="4" class="text-center">
                        No users found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

        {{ $users->withQueryString()->links('pagination::bootstrap-5') }}

    </div>

</div>

@endsection