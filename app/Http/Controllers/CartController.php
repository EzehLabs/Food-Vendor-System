<?php

namespace App\Http\Controllers;

use App\Models\Cart;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, $food_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::where('user_id', Auth::id())
                    ->where('food_id', $food_id)
                    ->first();

        if ($cart) {

            $cart->quantity += $request->quantity;
            $cart->save();

        } else {

            Cart::create([
                'user_id' => Auth::id(),
                'food_id' => $food_id,
                'quantity' => $request->quantity,
            ]);

        }
        $count = Cart::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Food added to cart successfully!',
            'count'=>Cart::where('user_id',auth()->id())->count()
        ], 200);
    }

    public function index()
    {
        $carts = Cart::where('user_id', Auth::id())->with('food')->get();
        return view('customer.cart.index', compact('carts'));
    }

    public function remove($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        $count = Cart::where('user_id', Auth::id())->sum('quantity');

        // return back();

        return response()->json([
            'message' => 'Item removed.',
            'count' => $count
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = Cart::with('food')->findOrFail($id);

        $cart->quantity = $request->quantity;
        $cart->save();

        $subtotal = $cart->quantity * $cart->food->price;

        $total = Cart::where('user_id', Auth::id())
            ->with('food')
            ->get()
            ->sum(function($item){
                return $item->quantity * $item->food->price;
            });

        $count = Cart::where('user_id', Auth::id())->sum('quantity');

        return response()->json([
            'subtotal'=>$subtotal,
            'total'=>$total,
            'count'=>$count,
            'message'=>'Cart updated.'
        ]);
    }

    public function sidebar()
    {
        $carts = Cart::where('user_id', Auth::id())
                    ->with('food')
                    ->get();

        return view('customer.cart.sidebar', compact('carts'));
    }
}
