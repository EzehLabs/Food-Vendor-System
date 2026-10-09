@extends('layouts.app')

@section('content')

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-success text-white">
            <h4 class="mb-0">
                <i class="fas fa-receipt"></i> Customer Orders
            </h4>
        </div>

        <div class="card-body">

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>Order No.</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($orders as $order)

                    <tr>

                        <td>{{ $order->order_number }}</td>

                        <td>{{ $order->user->name }}</td>

                        <td>₦{{ number_format($order->total_amount,2) }}</td>

                        <td>
                            <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'danger' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>

                        <td>
                            @php
                                $badge = match($order->status) {
                                    'pending_payment' => 'secondary',
                                    'paid' => 'primary',
                                    'accepted' => 'info',
                                    'preparing' => 'warning',
                                    'ready' => 'success',
                                    'completed' => 'dark',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp

                            <span class="badge bg-{{ $badge }}">
                                {{ ucwords(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>

                        <td>{{ $order->created_at->format('d M Y') }}</td>

                        <td>
                            <a href="{{ route('vendor.orders.show', $order->id) }}"
                               class="btn btn-primary btn-sm">
                                View
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No orders found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

            <div class="mt-3">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>

        </div>

    </div>

</div>

@endsection