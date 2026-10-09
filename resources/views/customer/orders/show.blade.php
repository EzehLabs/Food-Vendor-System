@extends('layouts.app')

@section('content')

<div class="card shadow">

    <div class="card-header bg-success text-white d-flex justify-content-between">

        <h4>Order Receipt</h4>

        <button onclick="window.print()" class="btn btn-light btn-sm">
            <i class="fas fa-print"></i> Print
        </button>

    </div>

    <div class="card-body">

        <h5>Food Vending System</h5>

        <hr>

        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('d M Y h:i A') }}</p>
        <p><strong>Status:</strong> {{ ucwords(str_replace('_',' ', $order->status)) }}</p>
        <p><strong>Payment:</strong> {{ ucfirst($order->payment_status) }}</p>

        <hr>

        <h5>Delivery Details</h5>

        <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Phone:</strong> {{ $order->phone }}</p>
        <p><strong>Address:</strong> {{ $order->delivery_address }}</p>

        <hr>

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Food</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>

            @foreach($order->items as $item)

                <tr>

                    <td>{{ $item->food->name }}</td>

                    <td>{{ $item->quantity }}</td>

                    <td>₦{{ number_format($item->price,2) }}</td>

                    <td>₦{{ number_format($item->price * $item->quantity,2) }}</td>

                </tr>

            @endforeach

            </tbody>

        </table>

        <h4 class="text-end">
            Total: ₦{{ number_format($order->total_amount,2) }}
        </h4>

        <a href="{{ route('customer.orders') }}" class="btn btn-secondary">
            Back
        </a>

    </div>

</div>

@endsection