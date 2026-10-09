@extends('layouts.app')

@section('content')

<!-- Page Content -->

    <h2>My Cart</h2>

    <table border="1">
    <tr>
        <th>Food</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
        <th>Action</th>
    </tr>

    @php $grandTotal = 0; @endphp

    @foreach($carts as $cart)
    <tr>
        <td>{{ $cart->food->name }}</td>
        <td>{{ $cart->quantity }}</td>
        <td>{{ $cart->food->price }}</td>
        <td>{{ $cart->quantity * $cart->food->price }}</td>
        <td>
            <a href="/cart/remove/{{ $cart->id }}">Remove</a>
        </td>
    </tr>

    @php $grandTotal += $cart->quantity * $cart->food->price; @endphp
    @endforeach

    </table>

    <h3>Total: ₦{{ $grandTotal }}</h3>

    <form method="POST" action="/checkout">
        @csrf
        <button type="submit">Checkout</button>
    </form>

@endsection
