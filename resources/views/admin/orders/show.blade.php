@extends('layouts.app')

@section('content')

<div class="card shadow">

    <div class="card-header bg-success text-white d-flex justify-content-between">

        <h4>Order Details</h4>

        <a href="{{ route('admin.orders') }}" class="btn btn-light btn-sm">
            Back
        </a>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>

                <p><strong>Customer:</strong> {{ $order->user->name }}</p>

                <p><strong>Email:</strong> {{ $order->user->email }}</p>

                <p><strong>Phone:</strong> {{ $order->phone }}</p>

                <p><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>

            </div>

            <div class="col-md-6">

                <p>
                    <strong>Payment Status:</strong>

                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'danger' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </p>

                <p>
                    <strong>Order Status:</strong>

                    <span class="badge bg-info">
                        {{ ucwords(str_replace('_', ' ', $order->status)) }}
                    </span>
                </p>

                <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y h:i A') }}</p>

            </div>

        </div>

        <hr>

        <h5>Ordered Items</h5>

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Food</th>
                    <th>Quantity</th>
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

        <div class="text-end">

            <h4>Total: ₦{{ number_format($order->total_amount,2) }}</h4>

        </div>

    </div>

</div>

@endsection