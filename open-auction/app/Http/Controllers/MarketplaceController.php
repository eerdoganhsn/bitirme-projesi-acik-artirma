<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Models\Auction;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'categories' => Category::all(),
            'auctions' => Auction::with(['product.category', 'product.shop', 'product.coverImage'])
                            ->latest()
                            ->take(4)
                            ->get(),
            'products' => Product::with(['category', 'shop', 'coverImage'])
                            ->where('listing_type', 'direct')
                            ->latest()
                            ->take(8)
                            ->get(),
        ]);
    }
    public function show($id)
    {
        // Ürünü; kategorisi, dükkanı, tüm resimleri ve varsa açık artırma bilgileriyle beraber getir
        $product = Product::with(['category', 'shop', 'images', 'auction.bids.user'])
            ->findOrFail($id);

        return Inertia::render('Product/Show', [
            'product' => $product
        ]);
    }
}