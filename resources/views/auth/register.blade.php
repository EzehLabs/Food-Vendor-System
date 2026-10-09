@extends('layouts.auth')

@section('content')

<div class="container-fluid">

    <div class="row min-vh-100">

        <!-- Left Side -->
        <div class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center auth-left">

            <div class="text-center text-white">

                <i class="fas fa-utensils fa-5x mb-4"></i>

                <h1 class="fw-bold">Food Vending System</h1>

                
                <!-- <p> Join the food </p> -->
                <p class="lead">
                    Fast • Easy • Secure Food Ordering
                </p>
                <br><br>
                <h2> Create an Account</h2>
                <p>
                    Join the Food Vending System and start buying or selling delicious meals.
                </p>
            </div>

        </div>

        <!-- Right Side -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center auth-right">

                <div class="card shadow-lg border-0 auth-card">

                    <div class="card-body p-5">

                        <h3 class="text-center mb-4">
                            Create Account
                        </h3>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <ul class="nav nav-pills nav-fill mb-4" id="registerTabs">

                            <li class="nav-item">
                                <button
                                    class="nav-link {{ old('company_name') ? '' : 'active' }}"
                                    id="customer-tab"
                                    type="button">

                                    Customer

                                </button>
                            </li>

                            <li class="nav-item">
                                <button
                                    class="nav-link {{ old('company_name') ? 'active' : '' }}"
                                    id="vendor-tab"
                                    type="button">

                                    Vendor

                                </button>
                            </li>

                        </ul>

                        <div id="customerForm" class="{{ old('company_name') ? 'd-none' : '' }}">

                            @include('auth.register-customer')

                        </div>

                        <div id="vendorForm" class="{{ old('company_name') ? '' : 'd-none' }}">

                            @include('auth.register-vendor')

                        </div>

                        
                        <br>
                        <hr>

                        <div class="text-center">

                            Already have an account?

                            <a href="{{ route('login') }}">
                                Login
                            </a>

                        </div>

                    </div>

                </div>

            

        </div>

    </div>

</div>
<script>
    const customerTab = document.getElementById('customer-tab');
    const vendorTab = document.getElementById('vendor-tab');

    const customerForm = document.getElementById('customerForm');
    const vendorForm = document.getElementById('vendorForm');

    customerTab.addEventListener('click', () => {

        customerTab.classList.add('active');
        vendorTab.classList.remove('active');

        customerForm.classList.remove('d-none');
        vendorForm.classList.add('d-none');

    });

    vendorTab.addEventListener('click', () => {

        vendorTab.classList.add('active');
        customerTab.classList.remove('active');

        vendorForm.classList.remove('d-none');
        customerForm.classList.add('d-none');

    });
</script>
@endsection