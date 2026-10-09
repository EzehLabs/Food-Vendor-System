@extends('layouts.app')

@section('content')

<h2 class="mb-4">Admin Dashboard</h2>

<div class="row">

    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Customers</h5>
                <h2>{{ $totalCustomers }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Total Vendors</h5>
                <h2>{{ $totalVendors }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5>Total Foods</h5>
                <h2>{{ $totalFoods }}</h2>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Total Orders</h5>
                <h2>{{ $totalOrders }}</h2>
            </div>
        </div>
    </div>

</div>

<div class="card">

    <div class="card-body">

        <h4>Total Revenue</h4>

        <h2 class="text-success">
            ₦{{ number_format($totalRevenue, 2) }}
        </h2>

    </div>

</div>

<div class="card mt-4">
    <div class="card-header">
        Monthly Orders
    </div>

    <div class="card-body">
        <canvas id="ordersChart"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('ordersChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            'Jan','Feb','Mar','Apr','May','Jun',
            'Jul','Aug','Sep','Oct','Nov','Dec'
        ],
        datasets: [{
            label: 'Orders',
            data: [
                @for($i = 1; $i <= 12; $i++)
                    {{ $monthlyOrders[$i] ?? 0 }},
                @endfor
            ],
            backgroundColor: '#198754'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

@endsection