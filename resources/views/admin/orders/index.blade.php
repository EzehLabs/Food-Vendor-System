@extends('layouts.app')

@section('content')

<h2 class="mb-4">Manage Orders</h2>

<div class="card">

    <div class="card-body">

        <form method="GET" class="row mb-3">

            <div class="col-md-10">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search by Order Number..."
                       value="{{ request('search') }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-success w-100">
                    Search
                </button>
            </div>

        </form>

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

                    <td>₦{{ number_format($order->total_amount, 2) }}</td>

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
                        <a href="{{ route('admin.orders.show', $order->id) }}"
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

        {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}

    </div>

</div>

@endsection