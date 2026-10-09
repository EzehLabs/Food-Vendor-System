@extends('layouts.auth')

@section('content')

<style>
    body{
        overflow:hidden;
    }

    .auth-left{
        background:#198754;
        color:#fff;
        display:flex;
        justify-content:center;
        align-items:center;
        flex-direction:column;
        padding:50px;
    }

    .auth-right{
        background:
            linear-gradient(rgba(0,0,0,.6), rgba(0,0,0,.6)),
            url("{{ asset('images/auth/1.jpg') }}");
        background-size:cover;
        background-position:center;
        background-repeat:no-repeat;
        transition:all .8s ease;
    }

    .overlay{
        min-height:100vh;
        display:flex;
        justify-content:center;
        align-items:center;
        padding:20px;
    }

    .auth-card{
        width:100%;
        max-width:500px;
        border:none;
        border-radius:20px;
        background:rgba(255,255,255,.95);
        backdrop-filter:blur(10px);
        box-shadow:0 20px 40px rgba(0,0,0,.3);
        padding:40px;
    }

    .icon-circle{
        width:90px;
        height:90px;
        border-radius:50%;
        background:#198754;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        margin:auto;
        font-size:35px;
    }

    .btn-success{
        border-radius:10px;
        padding:12px;
    }

    .btn-outline-secondary{
        border-radius:10px;
        padding:12px;
    }

    .small-text{
        font-size:15px;
        color:#666;
    }
</style>

<div class="container-fluid p-0">

    <div class="row g-0 vh-100">

        <!-- Left Side -->
        <div class="col-lg-5 auth-left d-none d-lg-flex">

            <i class="fas fa-utensils fa-5x mb-4"></i>

            <h2 class="fw-bold">
                Food Vending System
            </h2>

            <p class="text-center mt-3">
                One last step! Verify your email address to activate your account and start ordering or selling delicious meals.
            </p>

        </div>

        <!-- Right Side -->
        <div class="col-lg-7 auth-right">

            <div class="overlay">

                <div class="auth-card">

                    <div class="text-center">

                        <div class="icon-circle mb-4">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>

                        <h3 class="fw-bold">
                            Verify Your Email
                        </h3>

                        <p class="small-text mt-3">
                            Verification link sent to:
                        </p>

                        <h6 class="text-success mb-4">
                            {{ auth()->user()->email }}
                        </h6>

                        <p class="text-muted">
                            Click the link in your inbox to activate your account.
                            If you didn't receive it, you can request another verification email below.
                        </p>

                    </div>

                    @if (session('status') === 'registration-complete')
                        <div class="alert alert-success" role="status">
                            <i class="fas fa-check-circle me-2"></i>
                            Your account was registered successfully. We're sending a verification link now; please check your inbox shortly.
                        </div>
                    @elseif (session('status') === 'verification-link-sending')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            We're sending the verification email now. Please check your inbox shortly.
                        </div>
                    @endif
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('profile.edit') }}" class="text-success text-decoration-none">
                            Wrong email? Update your email address
                        </a>
                    </div>
                    <br>
                    <form method="POST"
                          action="{{ route('verification.send') }}">

                        @csrf

                        <button class="btn btn-success w-100">

                            <i class="fas fa-paper-plane me-2"></i>

                            Resend Verification Email

                        </button>

                    </form>

                    <form method="POST"
                          action="{{ route('logout') }}"
                          class="mt-3">

                        @csrf

                        <button class="btn btn-outline-secondary w-100">

                            <i class="fas fa-sign-out-alt me-2"></i>

                            Logout

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

const images = [
    "{{ asset('images/auth/1.jpg') }}",
    "{{ asset('images/auth/2.jpg') }}",
    "{{ asset('images/auth/3.jpg') }}",
    "{{ asset('images/auth/4.jpg') }}"
];

let current = 0;

const authRight = document.querySelector('.auth-right');

setInterval(() => {

    current = (current + 1) % images.length;

    authRight.style.opacity = "0.8";

    setTimeout(() => {

        authRight.style.backgroundImage =
            `linear-gradient(rgba(0,0,0,.6), rgba(0,0,0,.6)), url('${images[current]}')`;

        authRight.style.opacity = "1";

    }, 400);

}, 5000);

</script>

@endsection