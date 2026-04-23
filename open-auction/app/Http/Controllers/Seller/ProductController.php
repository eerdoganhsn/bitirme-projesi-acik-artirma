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
        $shop = auth()->user()->shop;
        $products = $shop->products()->with(['category', 'auction'])->latest()->get();

        return Inertia::render('Seller/Products/Index', [
            'products' => $products
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
        // 'images' alanını nullable yaptık ve dizi kontrolünü esnettik
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        'cover_index' => 'nullable|integer',
    ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $validated) {
            
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

            // C. YENİ BÖLÜM: Resimleri Storage'a yükle ve veritabanına kaydet
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
    // 1. Gelen dosyayı kontrol et
    if (!$request->hasFile('image')) {
        return response()->json(['error' => 'Dosya bulunamadı'], 400);
    }

    // 2. Doğrula (PNG/JPG/WebP ve 5MB limit)
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
    ]);

    // 3. 'public/temp_products' klasörüne kaydet
    $path = $request->file('image')->store('temp_products', 'public');

    // 4. Vue'ya dosya yolunu geri döndür
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

    // 4. Başarılı mesajıyla yönlendir
    return redirect()->route('seller.products.index')->with('success', 'Ürün başarıyla güncellendi.');
}

    public function destroy($id)
    {
        $product = \App\Models\Product::findOrFail($id);

        // GÜVENLİK: Başkası senin ürününü silemesin
        if ($product->shop_id !== auth()->user()->shop->id) {
            abort(403, 'Bu ürünü silme yetkiniz yok.');
        }

        // Ürünü sil (Eğer veritabanında onDelete('cascade') varsa bağlı açık artırma da silinir)
        $product->delete();

        return redirect()->route('seller.products.index');
    }
}