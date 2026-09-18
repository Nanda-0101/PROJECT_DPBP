<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $products   = Product::active()->with('category')->get();
        $trending   = Product::active()->trending()->take(4)->get();
        $trendGrid  = Product::active()->orderByDesc('is_trending')->take(8)->get();
        $categories = Category::orderBy('sort_order')->get();

        $creators = User::where('role', 'mitra')
            ->withCount('products')
            ->take(4)
            ->get();

        $stats = [
            'products' => Product::active()->count(),
            'creators' => User::where('role', 'mitra')->count() * 12,
            'rating'   => 4.9,
        ];

        return view('index', compact(
            'products', 'trending', 'trendGrid', 'categories', 'creators', 'stats'
        ));
    }
}