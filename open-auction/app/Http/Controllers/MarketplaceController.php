<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use App\Models\Auction;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Bid;

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
        $request->validate([
            'amount' => ['required', 'numeric', 'min:' . ($auction->current_price + 1)],
        ], [
            'amount.min' => 'Teklifiniz güncel fiyattan yüksek olmalıdır!'
        ]);

        if ($auction->status !== 'active' || now()->greaterThan($auction->end_time)) {
            return back()->withErrors(['amount' => 'Bu ihale sona ermiştir, teklif verilemez.']);
        }

        // ==========================================
        // YENİ: BİZDEN ÖNCEKİ LİDERİ BUL
        // ==========================================
        $previousHighestBid = $auction->bids()->orderBy('amount', 'desc')->first();

        Bid::create([
            'auction_id' => $auction->id,
            'user_id' => auth()->id(),
            'amount' => $request->amount,
        ]);

        $auction->update([
            'current_price' => $request->amount,
        ]);

        // ==========================================
        // YENİ: ESKİ LİDERE BİLDİRİM GÖNDER
        // Eğer önceki bir teklif varsa VE o kişi biz değilsek (kendi teklifimizi geçmiyorsak)
        // ==========================================
        if ($previousHighestBid && $previousHighestBid->user_id !== auth()->id()) {
            $previousHighestBid->user->notify(new \App\Notifications\OutbidNotification(
                $auction->product->title, 
                $auction->product->id, 
                $request->amount
            ));
        }

        // Fiyatın değiştiğini herkese duyuran canlı yayın (Önceki adımda yapmıştık)
        broadcast(new \App\Events\BidUpdated($auction->id, $request->amount, auth()->user()->name));

        return back()->with('success', 'Teklifiniz başarıyla alındı!');
    }

public function toggleWatchlist(\App\Models\Product $product)
    {
        $user = auth()->user();

        // Kullanıcının bu ürünü zaten takip edip etmediğine bak
        $existing = \App\Models\Watchlist::where('user_id', $user->id)
                                         ->where('product_id', $product->id)
                                         ->first();

        if ($existing) {
            // Zaten listedeyse çıkar
            $existing->delete();
            return back()->with('success', 'Ürün favorilerinizden çıkarıldı.');
        } else {
            // Listede yoksa ekle
            \App\Models\Watchlist::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
            return back()->with('success', 'Ürün favorilerinize eklendi! ❤️');
        }
    }
}