@extends('layouts.app')
@section('content')
    <h2>My Food Items</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <form action="{{ url('/vendor/foods') }}" method="GET" class="d-flex">

            <input
                type="text"
                name="search"
                class="form-control me-2"
                placeholder="Search food..."
                value="{{ request('search') }}">

            <button class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>

        </form>

        <a href="{{ url('/vendor/foods/create') }}"
        class="btn btn-success">
            <i class="fas fa-plus"></i> Add Food
        </a>

    </div>

    <table class="table table-hover">
    <tr>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    @foreach($foods as $food)
    <tr>
        <td>
            @if($food->image)
                <img src="/images/foods/{{ $food->image }}" width="50">
            @endif
        </td>
        <td>{{ $food->name }}</td>
        <td>{{ $food->price }}</td>
        <td>{{ $food->quantity }}</td>
        <td>{{ $food->status }}</td>
        <td>
            <a href="{{ route('vendor.foods.edit', $food->id) }}"class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>

            <a href="{{ url('/vendor/foods/delete/'.$food->id) }}"
            class="btn btn-danger btn-sm"
            onclick="return confirm('Delete this food?')">
                <i class="fas fa-trash"></i> Delete
            </a>
        </td>
    </tr>
    @endforeach
    </table>
    <div class="mt-4 d-flex justify-content-center">
        {{ $foods->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    
@endsection