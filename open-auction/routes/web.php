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
use App\Http\Controllers\Seller\OrderController as SellerOrderController;

// ANASAYFA ARTIK SADECE BURADAN ÇALIŞIYOR
Route::get('/', [MarketplaceController::class, 'index'])->name('home');

// ========================================================
// ORTAK PANEL (DASHBOARD) - HEM MÜŞTERİ HEM SATICI İÇİN
// ========================================================
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    // --- 1. MÜŞTERİ VERİLERİ (Herkes için çekilir) ---
    $bidsCount = \App\Models\Bid::where('user_id', $user->id)
                    ->distinct('auction_id')
                    ->count('auction_id');

    $myBids = \App\Models\Bid::with(['auction.product.coverImage'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->get()
                    ->unique('auction_id') 
                    ->values();
                    
    $ordersCount = \App\Models\Order::where('user_id', $user->id)->count();

    $myOrders = \App\Models\Order::with(['items.product.coverImage']) 
                    ->where('user_id', $user->id)
                    ->latest()
                    ->take(5) 
                    ->get();

    $watchlists = \App\Models\Watchlist::with(['product.coverImage', 'product.auction'])
                    ->where('user_id', $user->id)
                    ->latest()
                    ->get();
                    
    $watchlistCount = $watchlists->count();

    // --- 2. SATICI VERİLERİ (Sadece mağazası olanlar için çekilir) ---
    $sellerStats = [];
    $shopProducts = [];
    $recentOrders = [];
    
    // Eğer kullanıcı satıcıysa ve mağazası varsa verileri doldur
    if ($user->is_seller && $user->shop) {
        $shop = $user->shop;
        
        $sellerStats = [
            'activeProducts' => $shop->products()->where('status', 'active')->count(),
            'activeAuctions' => $shop->products()->where('listing_type', 'auction')->where('status', 'active')->count(),
            'pendingOrders' => \App\Models\OrderItem::whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })->where('status', 'pending')->count()
        ];

        $shopProducts = $shop->products()->with(['coverImage'])->latest()->take(5)->get();

        // Satıcıya Gelen Son Siparişler
        $recentOrders = \App\Models\OrderItem::with(['order.user', 'product.coverImage'])
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })
            ->latest()
            ->take(5)
            ->get();
    }

    // --- 3. VUE'YA GÖNDERİM ---
    return Inertia::render('Dashboard', [
        'user' => $user, 
        'stats' => [
            'totalBids' => $bidsCount,
            'watchlistCount' => $watchlistCount,
            'ordersCount' => $ordersCount, 
        ],
        'myBids' => $myBids,
        'myOrders' => $myOrders,
        'watchlists' => $watchlists,
        
        // Satıcı Propları (Vue'daki Dashboard.vue dosyasının defineProps kısmında tanımlı olmalıdır)
        'sellerStats' => $sellerStats,
        'shopProducts' => $shopProducts,
        'recentOrders' => $recentOrders, 
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


// ========================================================
// PROFİL İŞLEMLERİ
// ========================================================
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

// ========================================================
// SATICIYA ÖZEL ROTALAR (GÜVENLİK DUVARI EKLENDİ)
// ========================================================
// Sadece giriş yapmış (auth) VE satıcı yetkisine sahip (seller) olanlar girebilir!
Route::middleware(['auth', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    // Ürün Yönetimi
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store'); 
    Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
    Route::post('/products/temp-upload', [SellerProductController::class, 'tempUpload'])->name('products.temp-upload');
    Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    
    // Sipariş Yönetimi
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{id}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.update-status');
});

// ========================================================
// NORMAL ÜYELER İÇİN GENEL İŞLEM ROTALARI
// ========================================================
Route::middleware(['auth'])->group(function () {
    // Normal üyeler için teklif rotası
    Route::post('/auctions/{auction}/bid', [MarketplaceController::class, 'placeBid'])->name('auctions.bid');
    
    // İzleme Listesine Ekle/Çıkar
    Route::post('/watchlist/{product}/toggle', [MarketplaceController::class, 'toggleWatchlist'])->name('watchlist.toggle');
});

Route::get('/product/{id}', [MarketplaceController::class, 'show'])->name('product.show');
Route::get('/category/{id}', [MarketplaceController::class, 'category'])->name('category.show');
Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store');


// Yanlış bir URL girildiğinde çalışacak özel 404 sayfası
Route::fallback(function () {
    return \Inertia\Inertia::render('Error404');
});

require __DIR__.'/auth.php';