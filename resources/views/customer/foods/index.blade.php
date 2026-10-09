@extends('layouts.app')

@section('content')
<form method="GET" action="{{ url('/customer/home') }}" class="row mb-4">

    <div class="col-md-5">
        <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search food..."
            value="{{ request('search') }}">
    </div>

    <div class="col-md-4">
        <select name="category" class="form-select">

            <option value="">All Categories</option>

            <option value="Rice" {{ request('category')=='Rice'?'selected':'' }}>Rice</option>
            <option value="Drinks" {{ request('category')=='Drinks'?'selected':'' }}>Drinks</option>
            <option value="Snacks" {{ request('category')=='Snacks'?'selected':'' }}>Snacks</option>
            <option value="Fast Food" {{ request('category')=='Fast Food'?'selected':'' }}>Fast Food</option>

        </select>
    </div>

    <div class="col-md-3">
        <button class="btn btn-success w-100">
            <i class="fas fa-search"></i> Search
        </button>
    </div>

</form>
<div class="container">
    <h2 class="mb-4">Available Foods</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">

        @forelse($foods as $food)

        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">

            <div class="card h-100 border-0 shadow food-card">

                @if($food->image)
                    <img src="{{ asset('/images/foods/'.$food->image) }}"
                         class="card-img-top"
                         style="height:220px; object-fit:cover;">
                @else
                    <img src="https://via.placeholder.com/400x220?text=No+Image"
                         class="card-img-top">
                @endif

                <div class="card-body p-3">

                    <h6 class="fw-bold mb-1"> {{ $food->name }} </h6>

                     <small class="text-muted d-block mb-2"> {{ $food->vendor->company_name }} </small>

                   <div class="d-flex justify-content-between align-items-center mb-3">

                        <span class="fw-bold text-success">
                            ₦{{ number_format($food->price) }}
                        </span>

                        @if($food->status=='in_stock')
                            <span class="badge bg-success">
                                In Stock
                            </span>
                        @else
                            <span class="badge bg-danger">
                                Out
                            </span>
                        @endif

                    </div>

                    @if($food->status == 'in_stock')

                        <form class="add-to-cart-form" action="{{ url('/cart/add/'.$food->id) }}" method="POST">
                            @csrf

                            <div class="input-group-sm mb-2 quantity-box">

                                <button class="btn btn-outline-secondary qty-minus"
                                        type="button">
                                    -
                                </button>

                                <input type="text"
                                    name="quantity"
                                    value="1"
                                    class="form-control text-center quantity-input">

                                <button class="btn btn-outline-secondary qty-plus"
                                        type="button">
                                    +
                                </button>

                            </div>

                            <button type="submit" class="btn btn-primary w-100" data-food="{{ $food->id }}">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>

                    @else

                        <button class="btn btn-secondary w-100" disabled>
                            Not Available
                        </button>

                    @endif

                </div>

            </div>

        </div>

        @empty

        <div class="col-12">
            <div class="alert alert-info">
                No food available.
            </div>
        </div>

        @endforelse

    </div>

    <div class="text-end mt-3">

        <a href="{{ url('/cart') }}" class="btn btn-success">
            View Cart
        </a>

    </div>

</div>

<script>
    
</script>

@endsection

