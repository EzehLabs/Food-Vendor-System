@extends('layouts.app')

@section('content')

<h2 class="mb-4">Manage Foods</h2>

<div class="card">
    <div class="card-body">

        <form method="GET" class="row mb-3">

            <div class="col-md-10">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search food..."
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
                    <th>Image</th>
                    <th>Food</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            @forelse($foods as $food)

                <tr>

                    <td>
                        @if($food->image)
                            <img src="{{ asset('images/foods/'.$food->image) }}"
                                 width="60">
                        @endif
                    </td>

                    <td>{{ $food->name }}</td>
                    <td>{{ $food->category }}</td>
                    <td>{{ $food->vendor->name ?? 'N/A' }}</td>
                    <td>₦{{ number_format($food->price,2) }}</td>
                    <td>{{ $food->quantity }}</td>

                    <td>
                        <span class="badge bg-{{ $food->status == 'available' ? 'success' : 'danger' }}">
                            {{ ucfirst($food->status) }}
                        </span>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="text-center">
                        No foods found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

        {{ $foods->withQueryString()->links('pagination::bootstrap-5') }}

    </div>
</div>

@endsection