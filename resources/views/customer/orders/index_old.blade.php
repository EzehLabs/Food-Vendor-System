<h2>My Orders</h2>

@foreach($orders as $order)
<div>
    @foreach($order->items as $item)
    <p>Food: {{ $item->food->name }}</p>
    <p>Quantity: {{ $item->quantity }}</p>
    <p>Price: ₦{{ number_format($item->price, 2)}}</p>
    <p>Status: {{ $order->status }}</p>
    @endforeach
</div>
@endforeach