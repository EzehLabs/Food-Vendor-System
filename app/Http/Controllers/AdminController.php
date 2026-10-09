<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;

use Illuminate\Http\Request;



class AdminController extends Controller
{
    public function dashboard()
    {
        $topFoods = OrderItem::selectRaw('food_id, SUM(quantity) as total_sold')
            ->with('food')
            ->groupBy('food_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalFoods = Food::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        $monthlyOrders = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        return view('admin.dashboard', compact(
            'totalCustomers',
            'totalVendors',
            'totalFoods',
            'totalOrders',
            'totalRevenue',
            'monthlyOrders',
            'topFoods',
        ));
    }

    public function users(Request $request)
    {
        $users = User::query();

        if ($request->filled('search')) {
            $users->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $users->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function foods(Request $request)
    {
        $foods = Food::with('vendor');

        if ($request->filled('search')) {
            $foods->where('name', 'like', '%' . $request->search . '%');
        }

        $foods = $foods->latest()->paginate(10);

        return view('admin.foods.index', compact('foods'));
    }

    public function orders(Request $request)
    {
        $orders = Order::with('user');

        if ($request->filled('search')) {
            $orders->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $orders->latest()->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    
    public function showOrder($id)
    {
        $order = Order::with(['user', 'items.food'])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function payments(Request $request)
    {
        $payments = Order::with('user');

        if ($request->filled('search')) {
            $payments->where(function ($query) use ($request) {
                $query->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $payments = $payments->latest()->paginate(10);

        return view('admin.payments.index', compact('payments'));
    }

    public function reports()
    {
        $totalRevenue = Order::where('payment_status', 'paid')
            ->sum('total_amount');

        $totalOrders = Order::count();

        $totalCustomers = User::where('role', 'customer')->count();

        $totalVendors = User::where('role', 'vendor')->count();

        $totalFoods = Food::count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(5)
            ->get();

        $topFoods = OrderItem::selectRaw('food_id, SUM(quantity) as total_sold')
            ->with('food')
            ->groupBy('food_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        return view('admin.reports.index', compact(
            'totalRevenue',
            'totalOrders',
            'totalCustomers',
            'totalVendors',
            'totalFoods',
            'recentOrders',
            'topFoods'
        ));
    }
}