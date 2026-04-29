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
        $categories = Category::all();

        // 1. AKTİF İHALELER
        $auctions = Auction::with(['product.shop', 'product.category', 'product.coverImage'])
            ->where('status', 'active')
            ->where('end_time', '>', now())
            ->whereHas('product', function($query) {
                $query->where('status', 'active');
            })
            ->latest()
            ->take(4) 
            ->get();

        // 2. HEMEN AL ÜRÜNLERİ
        $products = Product::with(['shop', 'category', 'coverImage'])
            ->where('listing_type', 'direct')
            ->where('status', 'active')
            ->inRandomOrder() 
            ->take(4)
            ->get();

        // 3. SONA EREN İHALELER
        $finishedAuctions = Auction::with(['product.shop', 'product.category', 'product.coverImage'])
            ->where(function($query) {
                $query->where('status', 'ended')
                      ->orWhere('end_time', '<', now());
            })
            ->whereHas('product', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('end_time', 'desc')
            ->take(4)
            ->get();

        // Verileri Welcome görünümüne gönderiyoruz
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'categories' => $categories,
            'auctions' => $auctions,
            'products' => $products, 
            'finishedAuctions' => $finishedAuctions,
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
    public function placeBid(Request $request, Auction $auction)
    {
        // 1. Güvenlik Kontrolleri
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        if ($auction->status !== 'active' || now() > $auction->end_time) {
            return back()->withErrors(['message' => 'Bu ihale sona ermiş. Artık teklif verilemez.']);
        }

        if ($auction->product->shop->user_id === auth()->id()) {
            return back()->withErrors(['message' => 'Kendi ürününüze teklif veremezsiniz.']);
        }

        $minBid = $auction->current_price + 10; // En az 10 TL fazla vermeli
        if ($request->amount < $minBid) {
            return back()->withErrors(['message' => 'Teklifiniz güncel fiyattan en az 10 TL yüksek olmalıdır.']);
        }

        // 2. Teklifi Kaydet
        \App\Models\Bid::create([
            'auction_id' => $auction->id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
        ]);

        // 3. İhalenin Güncel Fiyatını Yenile
        $auction->update([
            'current_price' => $request->amount
        ]);

        return back()->with('success', 'Tebrikler! En yüksek teklifi siz verdiniz.');
    }
}