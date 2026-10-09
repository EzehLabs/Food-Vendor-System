<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Food;
use App\Models\OrderItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public function index() {
        $vendor = auth()->user()->vendor;

        $foods = Food::where('vendor_id', $vendor->id)->count();

        $orderItems = OrderItem::whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        });

        $totalOrders = $orderItems->count();

        $pendingOrders = (clone $orderItems)
            ->whereHas('order', function ($query) {
                $query->where('status', 'preparing');
            })
            ->count();

        $totalRevenue = 0;
        foreach ((clone $orderItems)->whereHas('order', function ($query) {
            $query->where('payment_status', 'paid');
        })->get() as $item) {
            $totalRevenue += $item->price * $item->quantity;
        }

        $recentOrders = OrderItem::with(['order.user', 'food'])->whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })->latest()->take(5)->get();

        $latestFoods = Food::where('vendor_id', $vendor->id)->latest()->take(5)->get();

        $confirmedOrders = OrderItem::whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })->whereHas('order', function ($query) {
            $query->where('status', 'confirmed');
        })->count();

        $preparingOrders = OrderItem::whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })->whereHas('order', function ($query) {
            $query->where('status', 'preparing');
        })->count();

        $outForDeliveryOrders = OrderItem::whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })->whereHas('order', function ($query) {
            $query->where('status', 'out_for_delivery');
        })->count();

        $deliveredOrders = OrderItem::whereHas('food', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })->whereHas('order', function ($query) {
            $query->where('status', 'delivered');
        })->count();



        return view('vendor.dashboard', compact(
            'foods',
            'totalOrders',
            'pendingOrders',
            'totalRevenue',
            'recentOrders',
            'latestFoods',
            'confirmedOrders',
            'preparingOrders',
            'outForDeliveryOrders',
            'deliveredOrders'
        ));
    }

    public function orders()
    {
        $vendorId = Auth::user()->vendor->id;

        $orders = Order::whereHas('items.food', function ($query) {
            $query->where('vendor_id', auth()->id());
        })
        ->with(['user', 'items.food'])
        ->latest()
        ->paginate(10);

        return view('vendor.orders.index', compact('orders'));
    }

    public function showOrder($id)
    {
        $order = Order::with(['user', 'items.food'])
            ->findOrFail($id);

        return view('vendor.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,accepted,preparing,ready,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);

        $order->status = $request->status;
        $order->save();

        return redirect()
            ->back()
            ->with('success', 'Order status updated successfully.');
    }
}
