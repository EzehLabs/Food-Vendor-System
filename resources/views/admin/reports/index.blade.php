@extends('layouts.app')

@section('content')

<h2 class="mb-4">Reports & Analytics</h2>

<div class="row">

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Total Revenue</h6>
                <h3>₦{{ number_format($totalRevenue,2) }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total Orders</h6>
                <h3>{{ $totalOrders }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning">
            <div class="card-body">
                <h6>Customers</h6>
                <h3>{{ $totalCustomers }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Vendors</h6>
                <h3>{{ $totalVendors }}</h3>
            </div>
        </div>
    </div>

</div>

<div class="card mb-4">
    <div class="card-header">
        Recent Orders
    </div>

    <div class="card-body">

        <table class="table table-striped">

            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            @foreach($recentOrders as $order)

                <tr>

                    <td>{{ $order->order_number }}</td>

                    <td>{{ $order->user->name }}</td>

                    <td>₦{{ number_format($order->total_amount,2) }}</td>

                    <td>{{ ucfirst($order->status) }}</td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

<div class="card mt-4">

    <div class="card-header bg-success text-white">
        Top Selling Foods
    </div>

    <div class="card-body">

        <table class="table table-striped">

            <thead>
                <tr>
                    <th>#</th>
                    <th>Food</th>
                    <th>Quantity Sold</th>
                </tr>
            </thead>

            <tbody>

                @forelse($topFoods as $index => $item)

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->food->name ?? 'Deleted Food' }}</td>
                    <td>{{ $item->total_sold }}</td>
                </tr>

                @empty

                <tr>
                    <td colspan="3" class="text-center">
                        No sales yet.
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection