@extends('layouts.app')

@section('content')
<div class="container">

    <h2>My Orders</h2>

    @forelse($orders as $order)

        <div class="card mb-3">
            <div class="card-header">
                <strong>{{ $order->order_number }}</strong>

                @if($order->status == 'pending_payment')
                    <span class="float-end badge bg-warning">Pending Payment</span>

                @elseif($order->status == 'paid')
                    <span class="float-end badge bg-primary">Paid</span>

                @elseif($order->status == 'preparing')
                    <span class="float-end badge bg-info">Preparing</span>

                @elseif($order->status == 'ready')
                    <span class="float-end badge bg-success">Ready</span>

                @elseif($order->status == 'completed')
                    <span class="float-end badge bg-dark">Completed</span>

                @elseif($order->status == 'cancelled')
                    <span class="float-end badge bg-danger">Cancelled</span>
                @endif
            </div>

            <div class="card-body">

                @foreach($order->items as $item)

                    <div class="d-flex justify-content-between">

                        <div>
                            <strong>{{ $item->food->name }}</strong>
                            <br>
                            Qty: {{ $item->quantity }}
                        </div>

                        <div>
                            ₦{{ number_format($item->price * $item->quantity,2) }}
                        </div>

                    </div>

                    <hr>

                @endforeach

                <h5>Total: ₦{{ number_format($order->total_amount,2) }}</h5>

            </div>
        </div>

    @empty

        <div class="alert alert-info">
            No orders yet.
        </div>

    @endforelse

</div>
@endsection