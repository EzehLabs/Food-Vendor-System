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

                            <div class="mb-3">

                                <label>Password</label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control"
                                        name="password"
                                        required>

                                    <button
                                        class="btn btn-outline-secondary"
                                        type="button"
                                        id="togglePassword">

                                        <i class="fas fa-eye"></i>

                                    </button>

                                </div>

                            </div>

                            <div class="d-flex justify-content-between mb-3">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="remember">

                                    <label class="form-check-label">

                                        Remember Me

                                    </label>

                                </div>

                                @if (Route::has('password.request'))

                                    <a href="{{ route('password.request') }}">
                                        Forgot Password?
                                    </a>

                                @endif

                            </div>

                            <button
                                type="submit"
                                class="btn btn-success w-100"
                                id="loginBtn">

                                Login

                            </button>

                        </form>
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

document.getElementById('togglePassword').onclick = function(){

    let password = document.getElementById('password');

    if(password.type === 'password'){

        password.type='text';

        this.innerHTML='<i class="fas fa-eye-slash"></i>';

    }else{

        password.type='password';

        this.innerHTML='<i class="fas fa-eye"></i>';

    }

};

document.getElementById('loginForm').onsubmit=function(){

    let btn=document.getElementById('loginBtn');

    btn.disabled=true;

    btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Signing In...';

}

</script>

@endsection