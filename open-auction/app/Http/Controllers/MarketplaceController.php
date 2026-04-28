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
        
        $product = Product::with(['category', 'shop', 'images', 'auction.bids.user'])
                    ->findOrFail($id);

        
        $relatedProducts = Product::with(['category', 'shop', 'coverImage'])
                            ->where('category_id', $product->category_id)
                            ->where('id', '!=', $id) // Kendisini gösterme
                            ->take(4) // 4 tane yeterli
                            ->get();

        return Inertia::render('Product/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            
            'comments' => $product->comments
        ]);
    }
    public function category($id)
    {
        $currentCategory = Category::findOrFail($id);
        
        $products = Product::with(['category', 'shop', 'coverImage', 'auction' ])
                    ->where('category_id', $id)
                    ->latest()
                    ->paginate(12);

        return Inertia::render('Category/Show', [
            'currentCategory' => $currentCategory,
            'products' => $products,
            'categories' => Category::all() // Sidebar için tüm kategoriler
        ]);
    }
}