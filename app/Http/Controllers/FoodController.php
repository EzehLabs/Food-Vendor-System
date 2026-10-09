<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Food;

use Illuminate\Support\Facades\Auth;

class FoodController extends Controller
{
    // Show all foods for vendor
    public function index(Request $request)
    {
        $vendorId = Auth::user()->vendor->id;

        $query = Food::where('vendor_id', $vendorId);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('price', 'like', '%' . $request->search . '%');
            });
        }

        $foods = $query->latest()->paginate(5);

        return view('vendor.foods.index', compact('foods'));
    }

    // Show add form
    public function create()
    {
        return view('vendor.foods.create');
    }

    // Store food
    public function store(Request $request)
    {
        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images/foods'), $imageName);
        }

        Food::create([
            'vendor_id' => Auth::user()->vendor->id,
            'name' => $request->name,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'expiry_date' => $request->expiry_date,
            'status' => $request->status,
            'image' => $imageName,
        ]);

        return redirect('/vendor/foods');
    }

    // Delete food
    public function destroy($id)
    {
        Food::find($id)->delete();
        return back();
    }

    // Control food customers see
    // public function customerView()
    // {
    //     $foods = Food::with('vendor')->get();
    //     return view('customer.foods.index', compact('foods'));
    // }

    public function customerView(Request $request)
    {
        $foods = Food::query();
        // $foods = Food::with('vendor')->get();

        if ($request->filled('search')) {
            $foods->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $foods->where('category', $request->category);
        }

        $foods = $foods->latest()->paginate(12);

        return view('customer.foods.index', compact('foods'));
    }

    public function edit($id)
    {
        $food = Food::findOrFail($id);

        return view('vendor.foods.edit', compact('food'));
    }

    public function update(Request $request, $id)
    {
        $food = Food::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            // 'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:in_stock,out_of_stock',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $food->name = $request->name;
        // $food->category = $request->category;
        $food->price = $request->price;
        $food->status = $request->status;

        if ($request->hasFile('image')) {

            // Delete old image
            if ($food->image && file_exists(public_path('images/foods/' . $food->image))) {
                unlink(public_path('images/foods/' . $food->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();

            $image->move(public_path('images/foods'), $imageName);

            $food->image = $imageName;
        }

        $food->save();

        return redirect('/vendor/foods')
                ->with('success', 'Food updated successfully.');
    }
}