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
            linear-gradient(rgba(0,0,0,.60), rgba(0,0,0,.60)),
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
    }

    .auth-card{
        width:100%;
        max-width:520px;
        background:rgba(255,255,255,.95);
        backdrop-filter:blur(10px);
        border:none;
        border-radius:20px;
        padding:40px;
        box-shadow:0 20px 40px rgba(0,0,0,.25);
    }

    .icon-circle{
        width:80px;
        height:80px;
        border-radius:50%;
        background:#198754;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        margin:auto;
        font-size:30px;
    }

    .form-control{
        border-radius:10px;
        height:55px;
    }

    .btn-success{
        border-radius:10px;
        height:50px;
        font-weight:600;
    }

    .password-wrapper{
        position:relative;
    }

    .toggle-password{
        position:absolute;
        right:18px;
        top:50%;
        transform:translateY(-50%);
        cursor:pointer;
        color:#6c757d;
        z-index:10;
    }

    #passwordStrength{
        font-size:.9rem;
        margin-top:6px;
    }

    .form-control[readonly]{
        background:#f8f9fa;
        cursor:not-allowed;
        opacity:1;
    }
</style>

<div class="container-fluid p-0">

    <div class="row g-0 vh-100">

        <!-- Left -->
        <div class="col-lg-5 auth-left d-none d-lg-flex">

            <i class="fas fa-lock fa-5x mb-4"></i>

            <h2 class="fw-bold">
                Create New Password
            </h2>

            <p class="text-center mt-3">
                Your new password should be strong and easy for you to remember.
            </p>

        </div>

        <!-- Right -->
        <div class="col-lg-7 auth-right">

            <div class="overlay">

                <div class="auth-card">

                    <div class="text-center mb-4">

                        <div class="icon-circle mb-3">
                            <i class="fas fa-key"></i>
                        </div>

                        <h3 class="fw-bold">
                            Reset Password
                        </h3>

                        <p class="text-muted">
                            Enter your new password below.
                        </p>

                    </div>

                    <form method="POST" action="{{ route('password.store') }}">

                        @csrf

                        <!-- Token -->
                        <input type="hidden"
                               name="token"
                               value="{{ request()->route('token') }}">

                        <!-- Email -->
                        <div class="form-floating mb-3">

                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email_display"
                                value="{{ old('email', request('email')) }}"
                                placeholder="Email"
                                readonly
                                required
                                autofocus>

                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email Address
                            </label>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>
                        <input
                            type="hidden"
                            name="email"
                            value="{{ old('email', request('email')) }}">

                        <!-- Password -->

                        <div class="form-floating mb-3 password-wrapper">

                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                placeholder="Password"
                                required>

                            <label for="password">
                                <i class="fas fa-lock me-2"></i>New Password
                            </label>

                            <span class="toggle-password"
                                  onclick="togglePassword('password',this)">
                                <i class="fas fa-eye"></i>
                            </span>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div id="passwordStrength"></div>

                        </div>

                        <!-- Confirm Password -->

                        <div class="form-floating mb-4 password-wrapper">

                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                placeholder="Confirm Password"
                                required>

                            <label for="password_confirmation">
                                <i class="fas fa-lock me-2"></i>Confirm Password
                            </label>

                            <span class="toggle-password"
                                  onclick="togglePassword('password_confirmation',this)">
                                <i class="fas fa-eye"></i>
                            </span>

                        </div>

                        <button class="btn btn-success w-100">

                            <i class="fas fa-save me-2"></i>

                            Reset Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

// Background slideshow
const images = [
    "{{ asset('images/auth/1.jpg') }}",
    "{{ asset('images/auth/2.jpg') }}",
    "{{ asset('images/auth/3.jpg') }}",
    "{{ asset('images/auth/4.jpg') }}"
];

let current = 0;

setInterval(() => {

    current = (current + 1) % images.length;

    document.querySelector('.auth-right').style.backgroundImage =
        `linear-gradient(rgba(0,0,0,.60), rgba(0,0,0,.60)),
        url('${images[current]}')`;

},5000);

// Toggle Password
function togglePassword(id, icon){

    let input = document.getElementById(id);

    if(input.type === "password"){
        input.type = "text";
        icon.innerHTML = '<i class="fas fa-eye-slash"></i>';
    }else{
        input.type = "password";
        icon.innerHTML = '<i class="fas fa-eye"></i>';
    }

}

// Password Strength
const password = document.getElementById('password');

password.addEventListener('keyup',function(){

    const value = this.value;

    const strength = document.getElementById('passwordStrength');

    if(value.length < 6){

        strength.innerHTML = "<span class='text-danger'>Weak Password</span>";

    }else if(value.length < 10){

        strength.innerHTML = "<span class='text-warning'>Medium Password</span>";

    }else{

        strength.innerHTML = "<span class='text-success'>Strong Password</span>";

    }

});

</script>

@endsection