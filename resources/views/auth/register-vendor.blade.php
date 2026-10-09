
@if ($errors->any())
    <div class="alert alert-danger">

        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach

    </div>
@endif

<form method="POST" action="/register/vendor" id="loginForm">
        @csrf

    <!-- Name -->
    <div class="form-floating mb-3">
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            placeholder="Full Name"
            value="{{ old('name') }}"
            required>

        <label for="name">  
            <i class="fas fa-user me-2"></i>Full Name
        </label>
    </div>

    <!-- Company Name -->
     <div class="form-floating mb-3">
        <input
            type="text"
            class="form-control"
            id="company_name"
            name="company_name"
            placeholder="Business Name"
            value="{{ old('company_name') }}"
            required>

        <label for="company_name">  
            <i class="fas fa-user me-2"></i>Business Name
        </label>
    </div>
    

    <!-- Email Address -->
    <div class="form-floating mb-3">
        <input type="email" 
            class="form-control" 
            id="email" 
            name="email" 
            placeholder="Email" 
            value="{{ old('email') }}" 
            required>

        <label for="email">
            <i class="fas fa-envelope me-2"></i>Email Address
        </label>
    </div>


    <div class="position-relative mb-3">

        <div class="form-floating">

            <input
                type="password"
                class="form-control pe-5"
                id="vend_password"
                name="password"
                placeholder="Password"
                required>

            <label for="password">
                <i class="fas fa-lock me-2"></i>Password
            </label>

        </div>

        <button
            type="button"
            id="toggle-password1"
            class="btn btn-link text-secondary position-absolute top-50 end-0 translate-middle-y me-2 toggle-password1"
            data-target="password">

            <i class="fas fa-eye"></i>

        </button>

    </div>

    <div class="position-relative mb-3">
        <div class="form-floating">
            <input
                type="password"
                name="password_confirmation"
                id="vend_password_confirmation"
                class="form-control pe-5"
                placeholder="Confirm Password"
                required>

            <label for="password_confirmation">
                <i class="fas fa-lock me-2"></i>Confirm Password
            </label>
            
        </div>

        <button
            type="button"
            id="toggle-password2"
            class="btn btn-link text-secondary position-absolute top-50 end-0 translate-middle-y me-2">

            <i class="fas fa-eye"></i>

        </button>

    </div>

    <button
        type="submit"
        class="btn btn-success w-100"
        id="loginBtn">

        Register

    </button>
</form>

<script>


    document.querySelector('#toggle-password1').onclick = function(){

        let password = document.getElementById('vend_password');

        if(password.type === 'password'){

            password.type='text';

            this.innerHTML='<i class="fas fa-eye-slash"></i>';

        }else{

            password.type='password';

            this.innerHTML='<i class="fas fa-eye"></i>';

        }
    }

    document.querySelector('#toggle-password2').onclick = function(){

        let password = document.getElementById('vend_password_confirmation');

        if(password.type === 'password'){

            password.type='text';

            this.innerHTML='<i class="fas fa-eye-slash"></i>';

        }else{

            password.type='password';

            this.innerHTML='<i class="fas fa-eye"></i>';

        }
    }

    document.getElementById('loginForm').onsubmit=function(){

        let btn=document.getElementById('loginBtn');

        btn.disabled=true;

        btn.innerHTML='<span class="spinner-border spinner-border-sm"></span> Signing In...';

    }

</script>

