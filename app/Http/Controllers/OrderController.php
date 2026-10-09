<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Cart;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Create an order from the customer's cart
     */
    public function checkout(Request $request)
    {
        $cartItems = Cart::where('user_id', Auth::id())
                        ->with('food')
                        ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $total = 0;

        foreach ($cartItems as $item) {
            $total += $item->food->price * $item->quantity;
        }

        $order = Order::create([
            'order_number'      => 'ORD-' . now()->format('YmdHis') . rand(1000, 9999),
            'user_id'           => Auth::id(),
            'total_amount'      => $total,
            'status'            => 'pending_payment',
            'payment_status'    => 'unpaid',
            'delivery_address'  => $request->delivery_address,
            'phone'             => $request->phone,
        ]);

        foreach ($cartItems as $item) {
            $order->items()->create([
                'food_id' => $item->food_id,
                'quantity' => $item->quantity,
                'price' => $item->food->price,
            ]);
        }

        return redirect()->route('payment.pay', ['order' => $order->id]);
    }

    /**
     * Redirect customer to Paystack
     */
    public function redirectToGateway(Request $request)
    {
        $order = Order::findOrFail($request->order);

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => Auth::user()->email,
                'amount' => $order->total_amount * 100,
                'callback_url' => route('payment.callback'),
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ]);

        if (!$response->successful()) {
            return back()->with('error', 'Unable to initialize payment.');
        }

        return redirect($response['data']['authorization_url']);
    }

    /**
     * Handle Paystack callback
     */
    public function handleGatewayCallback(Request $request)
    {
        $reference = $request->reference;

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->successful()) {
            return redirect('/customer/orders')
                ->with('error', 'Payment verification failed.');
        }

        $payment = $response['data'];

        $orderId = $payment['metadata']['order_id'];

        $order = Order::find($orderId);

        if ($payment['status'] == 'success') {

            $order->payment_status = 'paid';
            $order->status = 'processing';
            $order->payment_reference = $reference;
            $order->save();

            Cart::where('user_id', $order->user_id)->delete();

            return redirect('/customer/orders')
                ->with('success', 'Payment successful!');
        }

        return redirect('/customer/orders')
            ->with('error', 'Payment failed.');
    }

    /**
     * Customer orders
     */
    public function myOrders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.food'])
            ->where('id', $id)
            ->where('user_id', auth()->id()) // Prevent users from viewing others' orders
            ->firstOrFail();

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Customer payment history
     */
    public function transactions()
    {
        $orders = Order::where('user_id', Auth::id())
                    ->where('payment_status', 'paid')
                    ->latest()
                    ->get();

        return view('customer.transactions.index', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $order = Order::findOrFail($id);

        $order->status = $request->status;
        $order->save();

        return back()->with('success', 'Order status updated.');
    }
}