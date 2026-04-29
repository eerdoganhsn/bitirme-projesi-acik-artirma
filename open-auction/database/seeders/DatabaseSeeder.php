<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Comment;
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
            'email' => 'admin@admin.com',
            'password' => Hash::make('12345678'),
        ]);

        $adminShop = Shop::create([
            'user_id' => $adminUser->id,
            'store_name' => 'BidBod Resmi Mağazası',
            'status' => 'approved',
            'rating' => 5.0,
        ]);

        // 3. HEMEN ALINABİLİR TEST ÜRÜNLERİ (SEPET TESTİ İÇİN)
        // Migration dosyana göre 'direct' tipini kullanıyoruz
        for ($i = 1; $i <= 10; $i++) {
            Product::create([
                'shop_id' => $adminShop->id,
                'category_id' => rand(1, 5),
                'title' => 'Hemen Al Ürünü #' . $i,
                'description' => 'Bu ürün doğrudan sepete ekleyip satın alabileceğiniz bir "direct" üründür.',
                'listing_type' => 'direct', // Migration'daki 'direct' ile eşleşti
                'buy_now_price' => rand(100, 2000),
                'stock_quantity' => rand(5, 50),
                'status' => 'active'
            ]);
        }
        // 4. AÇIK ARTIRMA ÜRÜNLERİ VE TEKLİFLERİ
        $products = Product::all();
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

                $startingPrice = rand(100, 500);

                $auction = Auction::create([
                    'product_id' => $product->id,
                    'starting_price' => $startingPrice,
                    'current_price' => $startingPrice, // Şimdilik başlangıç fiyatına eşit
                    'start_time' => now(),
                    'end_time' => now()->addDays(rand(1, 7)),
                    'status' => 'active'
                ]);

                // --- YENİ EKLENEN KISIM: TEKLİFLERİ (BIDS) OLUŞTUR ---
                
                // Sisteme kayıtlı rastgele kullanıcılardan 3 tanesini seç (teklif verecek kişiler)
                $bidders = User::inRandomOrder()->take(3)->get();
                $currentBidAmount = $startingPrice;

                // Seçilen her bir kullanıcı sırayla teklif versin
                foreach ($bidders as $bidder) {
                    // Her yeni teklif, bir öncekinden 10 ila 50 TL daha fazla olsun
                    $currentBidAmount += rand(10, 50);

                    \App\Models\Bid::create([
                        'auction_id' => $auction->id,
                        'user_id' => $bidder->id,
                        'amount' => $currentBidAmount,
                        // Teklifleri de zamanda geriye doğru sıralı gösterelim diye eklendi
                        'created_at' => now()->subHours(rand(1, 24)) 
                    ]);
                }

                // İhalenin 'current_price' değerini en son verilen en yüksek teklifle güncelle
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
        

    $shop = Shop::first();
        $category = Category::first();
        $users = User::all(); // Tüm kullanıcıları al (Teklif verenler için)

        if ($shop && $category && $users->count() >= 3) {
            
            // Sona ermiş ürünler için örnek isimler
            $finishedProductNames = [
                'Geçmişin İzleri: Antika Gramofon',
                'Koleksiyonluk 1960 Model Gümüş Cep Saati',
                'Nadir Bulunan 1. Baskı Klasik Roman',
                'Orijinal İmzalı Beşiktaş Forması (2003)',
                'Osmanlı Dönemi El İşçiliği Bakır İbrik'
            ];

            // 5 adet bitmiş ihale oluşturalım
            foreach ($finishedProductNames as $index => $productName) {
                
                // 1. Ürünü Oluştur
                $finishedProduct = Product::create([
                    'shop_id' => $shop->id,
                    'category_id' => rand(1, 5), // Rastgele bir kategori
                    'title' => $productName,
                    'description' => 'Bu çok özel ürünün ihalesi başarıyla sona erdi. Kazanan şanslı kişiyi tebrik ederiz!',
                    'listing_type' => 'auction',
                    'status' => 'active', // Ürün listelenebilsin diye 'active' kalıyor
                ]);

                $startingPrice = rand(300, 1500);

                // 2. Bitmiş İhaleyi Oluştur
                $auction = Auction::create([
                    'product_id' => $finishedProduct->id,
                    'starting_price' => $startingPrice,
                    'current_price' => $startingPrice, 
                    'start_time' => now()->subDays(rand(10, 15)), // 10-15 gün önce başlamış
                    'end_time' => now()->subHours(rand(5, 72)), // 5 ile 72 saat önce BİTMİŞ
                    'status' => 'ended',
                ]);

                // 3. Bu bitmiş ihaleye 3 ila 6 arası rastgele teklif (Bid) ekle
                $bidCount = rand(3, 6);
                $bidders = User::inRandomOrder()->take($bidCount)->get();
                $currentBidAmount = $startingPrice;

                // Zamanı geriye doğru hesaplamak için (teklifler bitiş tarihinden önce verilmiş olmalı)
                $hoursBeforeEnd = rand(10, 48); 

                foreach ($bidders as $bidder) {
                    $currentBidAmount += rand(50, 250); // Müzayede çekişmeli geçmiş :)
                    $hoursBeforeEnd -= rand(1, 5); // Her teklif bir öncekinden birkaç saat sonra verilmiş
                    
                    // Saatin negatife düşmemesi için basit bir kontrol
                    if ($hoursBeforeEnd < 1) $hoursBeforeEnd = 1;

                    \App\Models\Bid::create([
                        'auction_id' => $auction->id,
                        'user_id' => $bidder->id,
                        'amount' => $currentBidAmount,
                        'created_at' => $auction->end_time->copy()->subHours($hoursBeforeEnd) // Bitiş saatinden önceye ayarla
                    ]);
                }

                // 4. İhalenin son fiyatını en yüksek teklife eşitle
                $auction->update([
                    'current_price' => $currentBidAmount
                ]);
            }
        }
    }
}