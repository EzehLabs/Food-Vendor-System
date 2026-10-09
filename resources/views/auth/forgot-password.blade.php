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
        position:relative;
        background-image:
            linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)),
            url('{{ asset('images/auth/1.jpg') }}');
        background-size:cover;
        background-position:center;
        background-repeat:no-repeat;
        transition:background-image .8s ease-in-out;
    }

    .auth-card{
        width:100%;
        max-width:460px;
        border:none;
        border-radius:20px;
        padding:35px;
        background:rgba(255,255,255,.96);
        box-shadow:0 20px 40px rgba(0,0,0,.25);
    }

    .form-control{
        border-radius:12px;
        height:55px;
    }

    .btn-success{
        border-radius:12px;
        height:50px;
        font-weight:600;
    }

    a{
        text-decoration:none;
    }
</style>

<div class="container-fluid p-0">

    <div class="row g-0 vh-100">

        <!-- Left Side -->
        <div class="col-lg-5 auth-left d-none d-lg-flex">

            <i class="fas fa-key fa-5x mb-4"></i>

            <h2 class="fw-bold">
                Forgot Your Password?
            </h2>

            <p class="text-center mt-3">
                No worries! Enter your email address and we'll send you a password reset link.
            </p>

        </div>

        <!-- Right Side -->
        <div class="col-lg-7 auth-right">

            <div class="d-flex justify-content-center align-items-center vh-100">

                <div class="auth-card">

                    <div class="text-center mb-4">

                        <i class="fas fa-envelope fa-3x text-success mb-3"></i>

                        <h3 class="fw-bold">
                            Reset Password
                        </h3>

                        <p class="text-muted">
                            Enter the email associated with your account.
                        </p>

                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">

                        @csrf

                        <div class="form-floating mb-4">

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="Email Address"
                                value="{{ old('email') }}"
                                required
                                autofocus>

                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>
                                Email Address
                            </label>

                        </div>

                        <button class="btn btn-success w-100">

                            <i class="fas fa-paper-plane me-2"></i>

                            Send Password Reset Link

                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <a href="{{ route('login') }}">

                            <i class="fas fa-arrow-left me-2"></i>

                            Back to Login

                        </a>

                    </div>

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

let index = 0;

setInterval(() => {

    index = (index + 1) % images.length;

    document.querySelector('.auth-right').style.backgroundImage =
        `linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)),
        url('${images[index]}')`;

}, 5000);

</script>

@endsection