<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product.coverImage')
            ->where('user_id', auth()->id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return Inertia::render('Checkout/Index', [
            'cartItems' => $cartItems
        ]);
    }
    public function store(Request $request)
    {
        // Doğrulama
        $request->validate([
            'full_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string',
            'phone' => 'required|string',
        ]);

        $cartItems = Cart::where('user_id', auth()->id())->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Sepetiniz boş.');
        }

        $totalAmount = $cartItems->reduce(function ($total, $item) {
            return $total + ($item->product->buy_now_price * $item->quantity);
        }, 0);

        // Veritabanı işlemi
        DB::transaction(function () use ($request, $cartItems, $totalAmount) {
            
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount' => $totalAmount,
                'full_name' => $request->full_name,
                'address' => $request->address,
                'city' => $request->city,
                'phone' => $request->phone,
                'status' => 'pending'
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->buy_now_price,
                ]);
            }

            // Sepeti boşalt
            Cart::where('user_id', auth()->id())->delete();
        });

        // Başarılı işlemden sonra Dashboard'a yönlendir
        return redirect()->route('dashboard')->with('success', 'Siparişiniz başarıyla oluşturuldu!');
    }
}
