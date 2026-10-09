<h2>Payment</h2>

<p>Order Number: {{ $order->order_number }}</p>

<p>Total: ₦{{ number_format($order->total_amount,2) }}</p>

<form action="{{ route('payment.process', $order->id) }}" method="POST">
    @csrf

    <div class="mb-3">
        <label>Delivery Address</label>
        <textarea name="delivery_address" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
        <label>Phone Number</label>
        <input type="text" name="phone" class="form-control" required>
    </div>

    <label>Payment Method</label>

    <select name="payment_method">
        <option value="card">Card</option>
        <option value="transfer">Bank Transfer</option>
        <option value="cash">Cash</option>
    </select>

    <br><br>

    <button type="submit">
        Pay Now
    </button>

</form>