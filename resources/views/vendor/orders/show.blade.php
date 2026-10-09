@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-success text-white">
            <h4>Order Details</h4>
        </div>

        <div class="card-body">

            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Customer:</strong> {{ $order->user->name }}</p>
            <p><strong>Phone:</strong> {{ $order->phone }}</p>
            <p><strong>Delivery Address:</strong> {{ $order->delivery_address }}</p>

            <!-- <hr> -->
            <hr>

            <h5>Update Order Status</h5>

            <form action="{{ route('vendor.orders.status', $order->id) }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-8">

                        <select name="status" class="form-select">

                            <option value="pending_payment" {{ $order->status == 'pending_payment' ? 'selected' : '' }}>
                                Pending Payment
                            </option>

                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>
                                Paid
                            </option>

                            <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>
                                Accepted
                            </option>

                            <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>
                                Preparing
                            </option>

                            <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>
                                Ready
                            </option>

                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>
                                Completed
                            </option>

                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>
                                Cancelled
                            </option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <button class="btn btn-success w-100">
                            Update Status
                        </button>

                    </div>

                </div>

            </form>

            <hr>

            <h5>Ordered Items</h5>

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
                        <td>₦{{ number_format($item->price, 2) }}</td>
                        <td>₦{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>

                @endforeach

                </tbody>

            </table>

            <h4 class="text-end">
                Total: ₦{{ number_format($order->total_amount, 2) }}
            </h4>

            <a href="{{ url('/vendor/orders') }}" class="btn btn-secondary">
                Back
            </a>

        </div>

    </div>

</div>

@endsection