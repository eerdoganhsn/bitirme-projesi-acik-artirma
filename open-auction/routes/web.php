<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\MarketplaceController;

Route::get('/', function () {
    $categories = Category::all();

    $auctions = Auction::with(['product.shop', 'product.category'])
        ->where('status', 'active')
        ->latest()
        ->take(4) 
        ->get();

    $products = Product::with(['shop', 'category'])
        ->where('status', 'active')
        ->inRandomOrder() 
        ->take(4)
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'categories' => $categories,
        'auctions' => $auctions,
        'products' => $products, 
    ]);
});


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Satıcıya özel rotalar (Sadece giriş yapmış ve mağazası olanlar)
Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store'); 
    Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
    Route::post('/products/temp-upload', [ProductController::class, 'tempUpload'])->name('products.temp-upload');
    Route::post('/products/temp-upload', [App\Http\Controllers\Seller\ProductController::class, 'tempUpload'])->name('products.temp-upload');
    Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');
});

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::get('/product/{id}', [MarketplaceController::class, 'show'])->name('product.show');
require __DIR__.'/auth.php';