<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Auction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        
        $categories = ['Elektronik', 'Antika', 'Kitap', 'Giyim', 'Koleksiyon'];
        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => strtolower($category)
            ]);
        }

        
        User::factory(10)->create();

        
        User::factory(5)->create()->each(function ($user) {
            $shop = Shop::create([
                'user_id' => $user->id,
                'store_name' => $user->name . ' Mağazası',
                'status' => 'approved',
                'rating' => rand(30, 50) / 10, 
            ]);

            
            for ($i = 0; $i < 3; $i++) {
                $product = Product::create([
                    'shop_id' => $shop->id,
                    'category_id' => rand(1, 5),
                    'title' => 'Örnek Ürün ' . rand(100, 999),
                    'description' => 'Bu harika bir açık artırma ürünüdür.',
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
    }
}