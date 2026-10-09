<?php

namespace App\Http\Controllers;

use App\Models\Food;

class HomeController extends Controller
{
    public function index()
    {
        $foods = Food::where('status','in_stock')
            ->latest()
            ->take(8)
            ->get();

        return view('welcome', compact('foods'));
    }
}