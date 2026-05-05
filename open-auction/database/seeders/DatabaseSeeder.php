<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Comment;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. KATEGORİLER
        $categories = ['Elektronik', 'Antika', 'Kitap', 'Giyim', 'Koleksiyon'];
        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => strtolower($category)
            ]);
        }
        
        // 2. SABİT YÖNETİCİ HESABI (TÜM ÜRÜNLERİN SAHİBİ SATICI)
        $adminUser = User::create([
            'name' => 'Hasan (Satıcı)',
            'email' => 'hasan@satis.com',
            'password' => Hash::make('12345678'),
            'is_seller' => true // İstersen migration'ında varsa ekle, yoksa sorun değil
        ]);

        $adminShop = Shop::create([
            'user_id' => $adminUser->id,
            'store_name' => 'BidBod Resmi Mağazası',
            'status' => 'approved',
            'rating' => 5.0,
        ]);

        // 3. MÜŞTERİ HESABI
        $hasanMusteri = User::create([
            'name' => 'Hasan (Müşteri)',
            'email' => 'hasan@musteri.com',
            'password' => Hash::make('12345678'), 
            'is_seller' => false,
        ]);

        // Ekstra Teklif Veren "Bot" Kullanıcılar (Sistemin canlı durması için)
        $botUsers = collect();
        for($i=1; $i<=3; $i++) {
            $botUsers->push(User::create([
                'name' => 'Teklifçi Bot ' . $i,
                'email' => "bot{$i}@test.com",
                'password' => Hash::make('12345678')
            ]));
        }

        // 4. HEMEN ALINABİLİR TEST ÜRÜNLERİ (TÜMÜ ADMIN SHOP'A AİT)
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'shop_id' => $adminShop->id, // BÜTÜN ÜRÜNLER BU MAĞAZAYA
                'category_id' => rand(1, 5),
                'title' => 'Hemen Al Ürünü #' . $i,
                'description' => 'Bu ürün doğrudan sepete ekleyip satın alabileceğiniz bir "direct" üründür.',
                'listing_type' => 'direct', 
                'buy_now_price' => rand(100, 2000),
                'stock_quantity' => rand(5, 50),
                'status' => 'active'
            ]);

            $this->createProductImages($product->id);
        }

        // 5. AÇIK ARTIRMA ÜRÜNLERİ VE TEKLİFLERİ (TÜMÜ ADMIN SHOP'A AİT)
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'shop_id' => $adminShop->id, // BÜTÜN ÜRÜNLER BU MAĞAZAYA
                'category_id' => rand(1, 5),
                'title' => 'İhale Ürünü ' . rand(100, 999),
                'description' => 'Bu bir açık artırma (auction) ürünüdür.',
                'listing_type' => 'auction',
                'stock_quantity' => 1,
                'status' => 'active'
            ]);

            $this->createProductImages($product->id);

            $startingPrice = rand(100, 500);

            $auction = Auction::create([
                'product_id' => $product->id,
                'starting_price' => $startingPrice,
                'current_price' => $startingPrice, 
                'start_time' => now(),
                'end_time' => now()->addDays(rand(1, 7)),
                'status' => 'active'
            ]);

            // Rastgele Teklifler (Hasan Müşteri ve Botlar)
            $currentBidAmount = $startingPrice;
            $allBidders = $botUsers->push($hasanMusteri); // Müşteri de ihalelere dahil olsun

            foreach ($allBidders->random(rand(2, 4)) as $bidder) {
                $currentBidAmount += rand(10, 50);

                \App\Models\Bid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $bidder->id,
                    'amount' => $currentBidAmount,
                    'created_at' => now()->subHours(rand(1, 24)) 
                ]);
            }

            $auction->update(['current_price' => $currentBidAmount]);
        }

        // 6. HASAN@MUSTERİ.COM İÇİN 5 ADET SİPARİŞ
        $orderStatuses = ['pending', 'processing', 'shipped', 'delivered', 'pending'];
        $orderTotals = [150, 450, 850, 120, 2500];
        
        foreach ($orderStatuses as $index => $status) {
            // Müşteriye Siparişi Oluştur
            $order = \App\Models\Order::create([
                'user_id' => $hasanMusteri->id,
                'order_number' => 'ORD-' . rand(10000, 99999), 
                'total_amount' => $orderTotals[$index],
                'full_name' => $hasanMusteri->name,                  
                'address' => 'Test Mahallesi, Deneme Sokak No:1', 
                'city' => 'Ankara',                            
                'phone' => '05551234567',                      
                'status' => 'pending', // Order genel durumu genelde pending kalabilir, item bazlı güncellenir
                'shipping_address' => 'Test Mahallesi, Deneme Sokak No:1, Ankara',
            ]);

            // Satıcının (hasan@satis.com) ürünlerinden rastgele birini seç ve siparişin içine at
            $randomProduct = Product::where('shop_id', $adminShop->id)->inRandomOrder()->first();
            
            if ($randomProduct && class_exists(\App\Models\OrderItem::class)) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $randomProduct->id,
                    'quantity' => 1,
                    'price' => $orderTotals[$index],
                    'status' => $status // SİPARİŞ DURUMU BURADA BELİRLENİYOR
                ]);
            }
        }
        
        // 7. RASTGELE YORUMLAR
        $sampleComments = ['Çok memnun kaldım.', 'Hızlı teslimat.', 'Kaliteli ürün.', 'Satıcıya teşekkürler.'];
        $products = Product::all();
        foreach ($products as $product) {
            Comment::create([
                'user_id' => $hasanMusteri->id,
                'product_id' => $product->id,
                'content' => $sampleComments[array_rand($sampleComments)],
                'rating' => rand(4, 5),
            ]);
        }
    }

    /**
     * Ürünlere otomatik resim ekleyen yardımcı fonksiyon
     */
    private function createProductImages($productId)
    {
        if (!class_exists(\App\Models\ProductImage::class)) return;

        // 1. Ana (Kapak) Resmi
        \App\Models\ProductImage::create([
            'product_id' => $productId,
            'image_path' => 'https://picsum.photos/seed/' . rand(1, 9999) . '/800/600',
            'is_cover' => true,
        ]);

        // 2. Galeri için 3 adet ekstra resim
        for ($j = 0; $j < 3; $j++) {
            \App\Models\ProductImage::create([
                'product_id' => $productId,
                'image_path' => 'https://picsum.photos/seed/' . rand(1, 9999) . '/800/600',
                'is_cover' => false,
            ]);
        }
    }
}