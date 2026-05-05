<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderItem;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Satıcının sadece kendi mağazasına ait sipariş kalemlerini listeler
     */
    public function index()
    {
        $user = auth()->user();
        
        // Kullanıcının mağazasını (shop) buluyoruz
        $shop = $user->shop; 

        // Eğer kullanıcının henüz bir mağazası yoksa boş liste gönder
        if (!$shop) {
            return Inertia::render('Seller/Orders/Index', [
                'sellerOrders' => []
            ]);
        }

        // Siparişleri bul: Sadece bu satıcının MAĞAZASINA (shop_id) ait ürünleri getir
        $sellerOrders = OrderItem::with(['order.user', 'product.coverImage'])
            ->whereHas('product', function ($query) use ($shop) {
                // HATA BURADAYDI: user_id yerine shop_id kullanmalıyız!
                $query->where('shop_id', $shop->id); 
            })
            ->latest()
            ->get();

        return Inertia::render('Seller/Orders/Index', [
            'sellerOrders' => $sellerOrders
        ]);
    }

    /**
     * Satıcı siparişin durumunu (Örn: Hazırlanıyor -> Kargolandı) günceller
     */
    public function updateStatus(Request $request, $orderItemId)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $orderItem = OrderItem::findOrFail($orderItemId);
        $user = auth()->user();
        
        // Güvenlik: Bu sipariş kalemi gerçekten bu satıcının MAĞAZASINA mı ait?
        if (!$user->shop || $orderItem->product->shop_id !== $user->shop->id) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        $orderItem->update(['status' => $request->status]);

        return back()->with('success', 'Sipariş durumu güncellendi.');
    }
}