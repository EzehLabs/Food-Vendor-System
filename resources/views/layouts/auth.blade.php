<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="auth-body">

    @yield('content')

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
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

            authRight.style.opacity = "0.7";

            setTimeout(() => {

                current = (current + 1) % images.length;

                authRight.style.backgroundImage =
                    `linear-gradient(rgba(0,0,0,.6), rgba(0,0,0,.6)), url('${images[current]}')`;

                authRight.style.opacity = "1";

            }, 3000);

        }, 8000);

    </script>
</body>
</html>