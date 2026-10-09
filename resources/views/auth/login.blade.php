@extends('layouts.auth')

@section('content')

<div class="container-fluid">

    <div class="row min-vh-100">

        <!-- Left Side -->
        <div class="col-lg-5 d-none d-lg-flex align-items-center justify-content-center auth-left">

            <div class="text-center text-white">

                <i class="fas fa-utensils fa-5x mb-4"></i>

                <h1 class="fw-bold">Food Vending System</h1>

                <p class="lead">
                    Fast • Easy • Secure Food Ordering
                </p>

            </div>

        </div>

        <!-- Right Side -->
        <div class="col-lg-7 d-flex align-items-center justify-content-center auth-right">

                <div class="card shadow-lg border-0 auth-card">

                    <div class="card-body p-5">

                        <h3 class="text-center mb-4">
                            Welcome Back
                        </h3>

                        @if (session('status') === 'login-link-sent')
                            <div class="alert alert-info" role="status">
                                If an account exists for that email, a sign-in link is on its way. The link expires in 15 minutes.
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">

                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach

                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm">

                            @csrf

                            <div class="mb-3">

                                <label>Email</label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>

                                    <input
                                        type="email"
                                        class="form-control"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus>

                                </div>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100"
                                id="loginBtn">

                                Email Me a Sign-in Link

                            </button>

                        </form>
                        <p class="text-muted text-center mt-3 mb-0">
                            We'll email you a secure link that expires in 15 minutes.
                        </p>
                        <br>
                        <hr>

                        <div class="text-center">

                            Don't have an account?

                            <a href="{{ route('register') }}">
                                Register
                            </a>

                        </div>

                    </div>

                </div>

            

        </div>

    </div>

</div>

<script>

document.getElementById('loginForm').onsubmit=function(){

    let btn=document.getElementById('loginBtn');

    btn.disabled=true;

    btn.innerHTML='<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Sending Link...';

}

</script>

@endsection