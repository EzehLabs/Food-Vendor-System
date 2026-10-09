@extends('layouts.app')

@section('content')

<div class="card shadow">

    <div class="card-header bg-success text-white">
        <h4>My Orders</h4>
    </div>

    <div class="card-body">

        <table class="table table-hover">

            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

            @forelse($orders as $order)

                <tr>

                    <td>{{ $order->order_number }}</td>

                    <td>₦{{ number_format($order->total_amount,2) }}</td>

                    <td>
                        <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'danger' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-info">
                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                        </span>
                    </td>

                    <td>{{ $order->created_at->format('d M Y') }}</td>

                    <td>
                        <a href="{{ route('customer.orders.show', $order->id) }}"
                           class="btn btn-primary btn-sm">
                            View
                        </a>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center">
                        No orders found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

        {{ $orders->links('pagination::bootstrap-5') }}

    </div>

</div>

@endsection