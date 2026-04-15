<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $shop = auth()->user()->shop;
        $products = $shop->products()->with(['category', 'auction'])->latest()->get();

        return Inertia::render('Seller/Products/Index', [
            'products' => $products
        ]);
    }
}
