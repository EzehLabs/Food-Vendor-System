@extends('layouts.app')

@section('content')

<h2 class="mb-4">Manage Payments</h2>

<div class="card">

    <div class="card-body">

        <form method="GET" class="row mb-3">

            <div class="col-md-10">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by customer or order number..."
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
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

            @forelse($payments as $payment)

                <tr>

                    <td>{{ $payment->order_number }}</td>

                    <td>{{ $payment->user->name }}</td>

                    <td>₦{{ number_format($payment->total_amount, 2) }}</td>

                    <td>
                        <span class="badge bg-{{ $payment->payment_status == 'paid' ? 'success' : 'danger' }}">
                            {{ ucfirst($payment->payment_status) }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-info">
                            {{ ucwords(str_replace('_', ' ', $payment->status)) }}
                        </span>
                    </td>

                    <td>{{ $payment->created_at->format('d M Y') }}</td>

                </tr>

            @empty

                <tr>
                    <td colspan="6" class="text-center">
                        No payments found.
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

        {{ $payments->withQueryString()->links('pagination::bootstrap-5') }}

    </div>

</div>

@endsection