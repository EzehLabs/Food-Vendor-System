<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Food Vending System</title>

    <!-- {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}} -->

    <!-- {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> --}} -->

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}"> -->
    <!-- jQuery -->
    <script src="{{ asset('js/jquery-4.0.0.min.js') }}"></script>
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold" href="#"> Food Vending System </a>
        

        <button class="navbar-toggler"
                data-bs-toggle="collapse"
                data-bs-target="#navbar">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="navbar">

            <ul class="navbar-nav ms-auto">

                @auth

                    @if(auth()->user()->role == 'customer')

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/home') }}">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link position-relative"
                            href="#"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#cartSidebar"
                            aria-controls="cartSidebar">

                                <i class="fas fa-shopping-cart"></i> Cart

                                <span id="cart-count"
                                    class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle {{ (\App\Models\Cart::where('user_id', auth()->id())->sum('quantity') == 0) ? 'd-none' : '' }}">
                                    {{ \App\Models\Cart::where('user_id', auth()->id())->sum('quantity') }}
                                </span>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/customer/orders') }}">
                                <i class="fas fa-receipt"></i> Orders
                            </a>
                        </li>

                    @endif

                    @if(auth()->user()->role == 'vendor')

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/vendor/dashboard') }}">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/vendor/foods') }}">
                                <i class="fas fa-utensils"></i> My Foods
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/vendor/orders') }}">
                                <i class="fas fa-plus-circle"></i> Orders
                            </a>
                        </li>

                    @endif

                    @if(auth()->user()->role == 'admin')

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users') }}">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.foods') }}">
                                <i class="fas fa-utensils"></i> Foods
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.orders') }}">
                                <i class="fas fa-shopping-bag"></i> Orders
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.payments') }}">
                                <i class="fas fa-credit-card"></i> Payments
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.reports') }}">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>

                    @endif

                    <li class="nav-item">

                        <form action="{{ route('logout') }}" method="POST">

                            @csrf

                            <button class="btn btn-link nav-link">

                                <i class="fas fa-sign-out-alt"></i>Logout

                            </button>

                        </form>

                    </li>

                @endauth

            </ul>

        </div>

    </div>
</nav>

<div class="container mt-4">

    <!-- Bootstrap Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="toast" class="toast text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

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

    @yield('content')

</div>


@auth
    @if(auth()->user()->role == 'customer')
        <div id="cart-area">
            @include('customer.cart.sidebar')
        </div>
    @endif
@endauth


<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="{{ asset('/js/bootstrap.bundle.min.js') }}"></script>



<!-- overlayScrollbars -->
<!-- <script src="{{ asset('adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script> -->
<!-- AdminLTE App -->
<!-- <script src="{{ asset('adminlte/js/adminlte.js') }}"></script> -->

<!-- PAGE PLUGINS -->
<!-- jQuery Mapael -->
<!-- <script src="{{ asset('adminlte/plugins/jquery-mousewheel/jquery.mousewheel.js') }}"></script> -->
<!-- <script src="{{ asset('adminlte/plugins/raphael/raphael.min.js') }}"></script> -->
<!-- <script src="{{ asset('adminlte/plugins/jquery-mapael/jquery.mapael.min.js') }}"></script> -->
<!-- <script src="{{ asset('adminlte/plugins/jquery-mapael/maps/usa_states.min.js') }}"></script> -->
<!-- ChartJS -->
<!-- <script src="{{ asset('adminlte/plugins/chart.js/Chart.min.js') }}"></script> -->

<script>
    
    $(function () {
        console.log("jQuery Loaded");

        $(document).on('click', '#remove_item', function(e){

            e.preventDefault();

            let btn = $(this);
            console.log(btn.attr('url'));
            

            $.ajax({
                url: btn.attr('url'),
                type: 'GET', // or DELETE if your route uses DELETE
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept':'application/json'
                },
                success:function(response){

                    showToast(response.message,'success');

                    $('#cart-count').text(response.count);

                    if(response.count > 0){
                        $('#cart-count').removeClass('d-none');
                    }else{
                        $('#cart-count').addClass('d-none');
                    }

                    // Reload the sidebar
                    
                    $('#cart-items').load('/cart/sidebar #cart-items > *');
                    $('#total_price').load('/cart/sidebar #total_price');
                    $('#cart-count').text(response.count);

                }
            });


        });


        $(document).on('submit', '.add-to-cart-form', function (e) {
            e.preventDefault();

            let form = $(this);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response){
                    showToast(response.message, 'success');
                    
                    $('#cart-count').text(response.count);
                    $("#cart-area").load("/cart/sidebar");
                    
                    if (response.count > 0) {
                        $('#cart-count').removeClass('d-none');
                    } else {
                        $('#cart-count').addClass('d-none');
                    }
                    
                },
                error: function(xhr){
                    let message = xhr.responseJSON?.message || 'Something went wrong!';
                    showToast(message, 'danger');
                }
            });
        });

        $(document).on('click','.qty-plus',function(){

            let input=$(this).siblings('.quantity-input');

            input.val(parseInt(input.val())+1);

        });

        $(document).on('click','.qty-minus',function(){

            let input=$(this).siblings('.quantity-input');

            let qty=parseInt(input.val());

            if(qty>1){
                input.val(qty-1);
            }

        });
    });

    function showToast(message, type) {
        const $toast = $('#toast');

        $toast.removeClass('text-bg-success text-bg-danger text-bg-warning text-bg-info');

        $toast.addClass('text-bg-' + type);
        $('#toastMessage').text(message);

        const toast = new bootstrap.Toast(document.getElementById('toast'));

        toast.show();

    }
</script>

</body>
</html>