@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4"> Vendor Dashboard</h2>
        <div class="row">

            <!-- TOTAL FOODS -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-primary shadow">
                    <div class="card-body text-center">
                        <h5 class="">Total Foods</h5>
                        <h2>{{ $foods }}</h2>
                    </div>
                </div>
            </div>

            <!-- TOTAL ORDERS -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-success shadow">
                    <div class="card-body text-center">
                        <h5 class="">Total Orders</h5>
                        <h2>{{ $totalOrders }}</h2>
                    </div>
                </div>
            </div>

            <!-- PENDING ORDERS -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-warning shadow">
                    <div class="card-body text-center">
                        <h5 class="">Pending Orders</h5>
                        <h2>{{ $pendingOrders }}</h2>
                    </div>
                </div>
            </div>

            <!-- TOTAL REVENUE -->
            <div class="col-md-3 mb-4">
                <div class="card text-white bg-danger shadow">
                    <div class="card-body text-center">
                        <h5 class="">Total Revenue</h5>
                        <h2> ₦{{ number_format($totalRevenue,2) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">

            <div class="col-md-4">
                <a href="{{ url('/vendor/foods/create') }}" class="btn btn-success w-100 py-3">
                    <i class="fas fa-plus-circle"></i><br>
                    Add New Food
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ url('/vendor/foods') }}" class="btn btn-primary w-100 py-3">
                    <i class="fas fa-utensils"></i><br>
                    Manage Foods
                </a>
            </div>

            <div class="col-md-4">
                <a href="{{ route('vendor.orders') }}" class="btn btn-warning w-100 py-3 text-dark">
                    <i class="fas fa-shopping-bag"></i><br>
                    View Orders
                </a>
            </div>

        </div>

        <div class="row">
            <div class="card shadow mt-4">

                <div class="card-header">
                    <h5 class="mb-0">Order Status Overview</h5>
                </div>

                <div class="card-body">

                    <div class="row text-center">

                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary">{{ $confirmedOrders }}</h3>
                                <p class="mb-0">Confirmed</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-warning">{{ $preparingOrders }}</h3>
                                <p class="mb-0">Preparing</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-info">{{ $outForDeliveryOrders }}</h3>
                                <p class="mb-0">Out for Delivery</p>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success">{{ $deliveredOrders }}</h3>
                                <p class="mb-0">Delivered</p>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <div class="row">
            <!-- Recent Orders -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Recent Orders</h5>
                </div>

                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order No. </th>
                                <th>Customer</th>
                                <th>Food</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            @forelse ($recentOrders as $item)
                                <tr>
                                    <td>{{ $item->order->order_number }}</td>
                                    <td>{{ $item->order->user->name }}</td>
                                    <td>{{ $item->food->name }}</td>
                                    <td>{{ $item->quantity}}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ ucfirst(str_replace('_', ' ', $item->order->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="5"> No recent orders. </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Latest Foods -->
            <div class="card shadow mt-4">

                <div class="card-header">
                    <h5 class="mb-0">Latest Foods</h5>
                </div>

                <div class="card-body">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($latestFoods as $food)

                                <tr>

                                    <td width="80">
                                        @if($food->image)
                                            <img src="{{ asset('images/foods/'.$food->image) }}"
                                                width="60"
                                                height="60"
                                                class="rounded"
                                                style="object-fit:cover;">
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>

                                    <td>{{ $food->name }}</td>

                                    <td>₦{{ number_format($food->price, 2) }}</td>

                                    <td>
                                        @if($food->status == 'in_stock')
                                            <span class="badge bg-success">In Stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="text-center">
                                        No foods added yet.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

@endsection