@extends('layouts.landing')

@section('content')

<!-- Hero -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <h1 class="display-4 fw-bold">
                    Delicious Meals Delivered To Your Doorstep
                </h1>

                <p class="lead">
                    Order food from trusted vendors around the campus in minutes.
                </p>

                <a href="{{ route('login') }}" class="btn btn-light btn-lg">
                    Order Food
                </a>

                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">
                    Become a Vendor
                </a>
            </div>

            <div class="col-lg-6 text-center">
                <img src="{{ asset('images/auth/1.jpg') }}"
                     class="img-fluid"
                     style="max-height:450px;">
            </div>

        </div>
    </div>
</section>

<!-- Features -->
<section class="container py-5">

    <div class="row text-center">

        <div class="col-md-3">
            <i class="fas fa-utensils fa-3x text-success mb-3"></i>
            <h5>Fresh Meals</h5>
        </div>

        <div class="col-md-3">
            <i class="fas fa-truck fa-3x text-success mb-3"></i>
            <h5>Fast Delivery</h5>
        </div>

        <div class="col-md-3">
            <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
            <h5>Secure Payment</h5>
        </div>

        <div class="col-md-3">
            <i class="fas fa-store fa-3x text-success mb-3"></i>
            <h5>Trusted Vendors</h5>
        </div>

    </div>

</section>

<!-- Popular Foods -->
<section class="container pb-5">

    <h2 class="text-center mb-4">
        Popular Foods
        <hr style="width: 60%; opacity: 60%;">
    </h2>
    

    <div class="row">

        @foreach($foods as $food)

        <div class="col-lg-3 col-md-4 col-6 mb-4">

            <div class="card shadow-sm border-0 h-100">

                <img src="{{ asset('images/foods/'.$food->image) }}"
                     class="card-img-top"
                     style="height:180px;object-fit:cover;">

                <div class="card-body">

                    <h6>{{ $food->name }}</h6>

                    <small class="text-muted">
                        {{ $food->vendor->company_name }}
                    </small>

                    <h5 class="text-success mt-2">
                        ₦{{ number_format($food->price,2) }}
                    </h5>

                </div>

            </div>

        </div>

        @endforeach

    </div>

</section>

<!-- CTA -->
<section class="bg-success text-white py-5">

    <div class="container text-center">

        <h2>Want to Sell Your Food?</h2>

        <p>Join hundreds of vendors already serving customers.</p>

        <a href="{{ route('register') }}"
           class="btn btn-light btn-lg">
            Become a Vendor
        </a>

    </div>

</section>

@endsection