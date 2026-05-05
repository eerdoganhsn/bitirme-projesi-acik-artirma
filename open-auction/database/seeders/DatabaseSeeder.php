<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Comment;
use App\Models\ProductImage; // Resim modeli eklendi
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
        
        // 2. SABİT YÖNETİCİ HESABI
        $adminUser = User::create([
            'name' => 'Pazar Yöneticisi',
            'email' => 'hasan@satis.com',
            'password' => Hash::make('12345678'),
        ]);

        $adminShop = Shop::create([
            'user_id' => $adminUser->id,
            'store_name' => 'BidBod Resmi Mağazası',
            'status' => 'approved',
            'rating' => 5.0,
        ]);

        // 3. HEMEN ALINABİLİR TEST ÜRÜNLERİ (SEPET TESTİ İÇİN)
        for ($i = 1; $i <= 10; $i++) {
            $product = Product::create([
                'shop_id' => $adminShop->id,
                'category_id' => rand(1, 5),
                'title' => 'Hemen Al Ürünü #' . $i,
                'description' => 'Bu ürün doğrudan sepete ekleyip satın alabileceğiniz bir "direct" üründür.',
                'listing_type' => 'direct', 
                'buy_now_price' => rand(100, 2000),
                'stock_quantity' => rand(5, 50),
                'status' => 'active'
            ]);

            // --- ÜRÜN RESİMLERİ (1 Kapak, 3 Galeri) ---
            $this->createProductImages($product->id);
        }

        // 4. AÇIK ARTIRMA ÜRÜNLERİ VE TEKLİFLERİ
        $users = User::all();
        User::factory(5)->create()->each(function ($user) use ($users) {
            $shop = Shop::create([
                'user_id' => $user->id,
                'store_name' => $user->name . ' Mağazası',
                'status' => 'approved',
                'rating' => rand(30, 50) / 10, 
            ]);

            for ($i = 0; $i < 2; $i++) {
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'category_id' => rand(1, 5),
                    'title' => 'İhale Ürünü ' . rand(100, 999),
                    'description' => 'Bu bir açık artırma (auction) ürünüdür.',
                    'listing_type' => 'auction',
                    'stock_quantity' => 1,
                    'status' => 'active'
                ]);

                // --- ÜRÜN RESİMLERİ (1 Kapak, 3 Galeri) ---
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

                $bidders = clone $users; // Mevcut kullanıcıları kopyala
                $bidders = $bidders->random(min(3, $bidders->count()));
                $currentBidAmount = $startingPrice;

                foreach ($bidders as $bidder) {
                    $currentBidAmount += rand(10, 50);

                    \App\Models\Bid::create([
                        'auction_id' => $auction->id,
                        'user_id' => $bidder->id,
                        'amount' => $currentBidAmount,
                        'created_at' => now()->subHours(rand(1, 24)) 
                    ]);
                }

                $auction->update([
                    'current_price' => $currentBidAmount
                ]);
            }
        });

        // 5. RASTGELE YORUMLAR
        $sampleComments = ['Çok memnun kaldım.', 'Hızlı teslimat.', 'Kaliteli ürün.', 'Satıcıya teşekkürler.'];
        $products = Product::all();
        $users = User::all();
        foreach ($products as $product) {
            Comment::create([
                'user_id' => $users->random()->id,
                'product_id' => $product->id,
                'content' => $sampleComments[array_rand($sampleComments)],
                'rating' => rand(4, 5),
            ]);
        }
        
        // 6. BİTMİŞ İHALELER
        $shop = Shop::first();
        $category = Category::first();
        
        if ($shop && $category && $users->count() >= 3) {
            $finishedProductNames = [
                'Geçmişin İzleri: Antika Gramofon',
                'Koleksiyonluk 1960 Model Gümüş Cep Saati',
                'Nadir Bulunan 1. Baskı Klasik Roman',
                'Orijinal İmzalı Beşiktaş Forması (2003)',
                'Osmanlı Dönemi El İşçiliği Bakır İbrik'
            ];

            foreach ($finishedProductNames as $index => $productName) {
                $finishedProduct = Product::create([
                    'shop_id' => $shop->id,
                    'category_id' => rand(1, 5), 
                    'title' => $productName,
                    'description' => 'Bu çok özel ürünün ihalesi başarıyla sona erdi. Kazanan şanslı kişiyi tebrik ederiz!',
                    'listing_type' => 'auction',
                    'status' => 'active', 
                ]);

                // --- ÜRÜN RESİMLERİ (1 Kapak, 3 Galeri) ---
                $this->createProductImages($finishedProduct->id);

                $startingPrice = rand(300, 1500);

                $auction = Auction::create([
                    'product_id' => $finishedProduct->id,
                    'starting_price' => $startingPrice,
                    'current_price' => $startingPrice, 
                    'start_time' => now()->subDays(rand(10, 15)), 
                    'end_time' => now()->subHours(rand(5, 72)), 
                    'status' => 'ended',
                ]);

                $bidCount = rand(3, 6);
                $bidders = User::inRandomOrder()->take($bidCount)->get();
                $currentBidAmount = $startingPrice;
                $hoursBeforeEnd = rand(10, 48); 

                foreach ($bidders as $bidder) {
                    $currentBidAmount += rand(50, 250); 
                    $hoursBeforeEnd -= rand(1, 5); 
                    
                    if ($hoursBeforeEnd < 1) $hoursBeforeEnd = 1;

                    \App\Models\Bid::create([
                        'auction_id' => $auction->id,
                        'user_id' => $bidder->id,
                        'amount' => $currentBidAmount,
                        'created_at' => $auction->end_time->copy()->subHours($hoursBeforeEnd) 
                    ]);
                }

                $auction->update([
                    'current_price' => $currentBidAmount
                ]);
            }
        }

        // 7. MÜŞTERİ PANELİ İÇİN TEST VERİSİ (HASAN KULLANICISI)
        $hasan = \App\Models\User::firstOrCreate(
            ['email' => 'hasan@musteri.com'], 
            [
                'name' => 'Hasan (Test Müşterisi)',
                'password' => bcrypt('12345678'), 
                'is_seller' => false,
            ]
        );

        $statuses = ['completed', 'shipping', 'pending'];
        $orderTotals = [450, 1250, 85];
        
        foreach ($statuses as $index => $status) {
            $order = \App\Models\Order::create([
                'user_id' => $hasan->id,
                'order_number' => 'ORD-' . rand(10000, 99999), 
                'total_amount' => $orderTotals[$index],
                'full_name' => $hasan->name,                   
                'address' => 'Test Mahallesi, Deneme Sokak No:1', 
                'city' => 'Ankara',                            
                'phone' => '05551234567',                      
                'status' => $status,
                'shipping_address' => 'Test Mahallesi, Deneme Sokak No:1, Ankara',
            ]);

            $randomProduct = \App\Models\Product::inRandomOrder()->first();
            if ($randomProduct && class_exists(\App\Models\OrderItem::class)) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $randomProduct->id,
                    'quantity' => 1,
                    'price' => $orderTotals[$index]
                ]);
            }
        }

        $activeAuctions = \App\Models\Auction::where('status', 'active')->take(2)->get();
        
        foreach ($activeAuctions as $auction) {
            $newBidAmount = $auction->current_price + rand(50, 100);
            
            \App\Models\Bid::create([
                'auction_id' => $auction->id,
                'user_id' => $hasan->id,
                'amount' => $newBidAmount,
            ]);
            $auction->update([
                'current_price' => $newBidAmount
            ]);
        }
    }

    /**
     * Ürünlere otomatik resim ekleyen yardımcı fonksiyon
     */
    private function createProductImages($productId)
    {
        // Modelin var olduğundan emin olalım ki hata vermesin
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