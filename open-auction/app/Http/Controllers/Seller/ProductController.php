<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            // Eğer mağaza yoksa boş liste döndür (İstersen burada başka bir sayfaya da yönlendirebilirsin)
            return Inertia::render('Seller/Products/Index', [
                'products' => [],
                'pendingOrdersCount' => 0,
                'recentOrders' => []
            ]);
        }

        // 1. Mağazanın aktif ürünlerini ve ihalelerini çek
        $products = $shop->products()->with(['category', 'auction'])->latest()->get();

        // 2. Bekleyen Siparişlerin Sayısını Çek (Sadece 'pending' olanlar)
        $pendingOrdersCount = \App\Models\OrderItem::whereHas('product', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })->where('status', 'pending')->count();

        // 3. Vitrin için Son Gelen 5 Siparişi Çek
        $recentOrders = \App\Models\OrderItem::with(['order.user', 'product.coverImage'])
            ->whereHas('product', function ($q) use ($shop) {
                $q->where('shop_id', $shop->id);
            })
            ->latest()
            ->take(5)
            ->get();

        // 4. Hepsini tek bir seferde Vue tarafına gönder
        return Inertia::render('Seller/Products/Index', [
            'products' => $products,
            'pendingOrdersCount' => $pendingOrdersCount,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        return Inertia::render('Seller/Products/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'listing_type' => 'required|in:direct,auction',
            'price' => 'required|numeric|min:1',
            'stock' => 'nullable|required_if:listing_type,direct|numeric|min:1',
            'end_date' => 'nullable|required_if:listing_type,auction|date|after:now',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'cover_index' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $validated) {
            
            // A. Ana ürünü kaydet
            $product = \App\Models\Product::create([
                'shop_id' => auth()->user()->shop->id,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => 'Satıcı tarafından eklendi.', 
                'listing_type' => $validated['listing_type'],
                'buy_now_price' => $validated['listing_type'] === 'direct' ? $validated['price'] : null,
                'status' => 'active',
            ]);

            // B. Açık artırma ise kaydet
            if ($validated['listing_type'] === 'auction') {
                \App\Models\Auction::create([
                    'product_id' => $product->id,
                    'starting_price' => $validated['price'], 
                    'current_price' => $validated['price'],
                    'start_time' => now(),                  
                    'end_time' => $validated['end_date'],    
                    'status' => 'active',
                ]);
            }

            // C. Resimleri Storage'a yükle ve veritabanına kaydet
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');
                    
                    // index, frontend'den gelen cover_index ile aynıysa kapak yap
                    $isCover = ($index === (int) $request->input('cover_index', 0)); 
                    
                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'is_cover' => $isCover,
                    ]);
                }
            }
        });

        return redirect()->route('seller.products.index');
    }

    public function tempUpload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'Dosya bulunamadı'], 400);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('temp_products', 'public');

        return response()->json([
            'path' => $path
        ]);
    }
    
    public function edit($id)
    {
        $product = \App\Models\Product::with(['auction', 'images'])->findOrFail($id);

        if ($product->shop_id !== auth()->user()->shop->id) {
            abort(403, 'Bu ürünü düzenleme yetkiniz yok.');
        }

        $categories = Category::all();

        return Inertia::render('Seller/Products/Edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = \App\Models\Product::findOrFail($id);

        if ($product->shop_id !== auth()->user()->shop->id) {
            abort(403, 'Bu ürünü düzenleme yetkiniz yok.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1',
            'stock' => 'nullable|numeric|min:1',
            'end_date' => 'nullable|date',
            'images' => 'required|array|min:1', 
            'cover_index' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated, $product, $request) {
            
            $product->update([
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'buy_now_price' => $product->listing_type === 'direct' ? $validated['price'] : null,
                'stock' => $validated['stock'],
            ]);

            if ($product->listing_type === 'auction') {
                $product->auction()->update([
                    'starting_price' => $validated['price'],
                    'current_price' => $validated['price'], 
                    'end_time' => $validated['end_date'],
                ]);
            }

            $product->images()->delete();

            foreach ($validated['images'] as $index => $path) {
                $product->images()->create([
                    'image_path' => $path,
                    'is_cover' => ($index === (int) $validated['cover_index']),
                ]);
            }
        });

        return redirect()->route('seller.products.index')->with('success', 'Ürün başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $product = \App\Models\Product::findOrFail($id);

        if ($product->shop_id !== auth()->user()->shop->id) {
            abort(403, 'Bu ürünü silme yetkiniz yok.');
        }

        $product->delete();

        return redirect()->route('seller.products.index');
    }
}