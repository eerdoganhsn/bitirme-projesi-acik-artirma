<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

// ANASAYFA ARTIK SADECE BURADAN ÇALIŞIYOR
Route::get('/', [MarketplaceController::class, 'index'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Sepet işlemleri için rotalar
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
});

// Ödeme işlemleri için rotalar
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Satıcıya özel rotalar
Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store'); 
    Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
    Route::post('/products/temp-upload', [SellerProductController::class, 'tempUpload'])->name('products.temp-upload');
    Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/auctions/{auction}/bid', [MarketplaceController::class, 'placeBid'])->name('auctions.bid');
});

Route::get('/product/{id}', [MarketplaceController::class, 'show'])->name('product.show');
Route::get('/category/{id}', [MarketplaceController::class, 'category'])->name('category.show');
Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store');

require __DIR__.'/auth.php';