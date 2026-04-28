<?php

namespace App\Http\Controllers;

use App\Models\Cart; // EKSİK OLAN VE HATAYA SEBEP OLAN SATIR BURASI!
use Inertia\Inertia;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Sepet Sayfası
    public function index()
    {
        $cartItems = Cart::with(['product.coverImage', 'product.shop'])
            ->where('user_id', auth()->id())
            ->get();

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems
        ]);
    }

    // Sepete Ekleme veya Miktar Artırma
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = Cart::where('user_id', auth()->id())
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            $cart->increment('quantity');
        } else {
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => 1
            ]);
        }

        
        return redirect()->route('cart.index')->with('success', 'Ürün sepetinize eklendi.');
    }

    // Sepetten Ürün Silme
    public function destroy($id)
    {
        $cartItem = Cart::where('user_id', auth()->id())->findOrFail($id);
        $cartItem -> delete();

        return back()->with('message', 'Ürün sepetten kaldırıldı.');
    }
}
