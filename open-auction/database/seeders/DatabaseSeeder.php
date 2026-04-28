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

        // 4. AÇIK ARTIRMA ÜRÜNLERİ
        // Migration'daki 'auction' ile eşleşti
        User::factory(5)->create()->each(function ($user) {
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

                Auction::create([
                    'product_id' => $product->id,
                    'starting_price' => rand(100, 500),
                    'current_price' => rand(100, 500),
                    'start_time' => now(),
                    'end_time' => now()->addDays(rand(1, 7)),
                    'status' => 'active'
                ]);
            }
        });

        // 5. RASTGELE YORUMLAR
        $products = Product::all();
        $users = User::all();
        $sampleComments = ['Çok memnun kaldım.', 'Hızlı teslimat.', 'Kaliteli ürün.', 'Satıcıya teşekkürler.'];

        foreach ($products as $product) {
            Comment::create([
                'user_id' => $users->random()->id,
                'product_id' => $product->id,
                'content' => $sampleComments[array_rand($sampleComments)],
                'rating' => rand(4, 5),
            ]);
        }
    }
}