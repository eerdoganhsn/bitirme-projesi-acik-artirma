<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Auction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseEndedAuctions extends Command
{
    /**
     * Komutun terminaldeki adı (Bunu terminale yazarak manuel de çalıştırabiliriz)
     */
    protected $signature = 'auctions:close';

    /**
     * Komutun açıklaması
     */
    protected $description = 'Süresi dolan ihaleleri kontrol eder, kapatır ve kazananlara sipariş oluşturur.';

    /**
     * Komut çalıştığında çalışacak asıl fonksiyon
     */
    public function handle()
    {
        $this->info('Süresi dolan ihaleler kontrol ediliyor...');

        // 1. Durumu hala 'active' olan VE bitiş tarihi geçmiş (şu andan küçük) olan ihaleleri bul
        // Teklifleri ve teklif veren kullanıcıları da beraberinde getir ki kimin kazandığını bilelim
        $endedAuctions = Auction::with(['product', 'bids.user'])
            ->where('status', 'active')
            ->where('end_time', '<=', now())
            ->get();

        if ($endedAuctions->isEmpty()) {
            $this->info('Kapatılacak ihale bulunamadı.');
            return;
        }

        $closedCount = 0;

        foreach ($endedAuctions as $auction) {
            DB::beginTransaction();

            try {
                // 2. İhalenin durumunu 'ended' (bitti) olarak güncelle
                $auction->update(['status' => 'ended']);

                // 3. İhaleye ait hiç teklif var mı kontrol et
                $highestBid = $auction->bids()->orderBy('amount', 'desc')->first();

                if ($highestBid) {
                    // TEKLİF VAR: Kazananı bulduk!
                    $winner = $highestBid->user;

                    // 4. Kazanan için otomatik bir "Bekleyen Sipariş" (Order) oluştur
                    $order = Order::create([
                        'user_id' => $winner->id,
                        'order_number' => 'AUC-' . strtoupper(uniqid()), // Benzersiz ihale sipariş numarası
                        'total_amount' => $highestBid->amount,
                        'status' => 'pending', // Sipariş beklemede (Müşteri girip ödeme yapacak/adres seçecek)
                        // Kullanıcının varsayılan bilgileri varsa buraya eklenebilir, şimdilik placeholder
                        'full_name' => $winner->name,
                        'phone' => 'Belirtilmedi',
                        'address' => 'Belirtilmedi',
                        'city' => 'Belirtilmedi',
                        'shipping_address' => 'Belirtilmedi',
                    ]);

                    // 5. Siparişin içine kazandığı ürünü (OrderItem) ekle
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $auction->product_id,
                        'quantity' => 1,
                        'price' => $highestBid->amount,
                    ]);

                    // (Opsiyonel) Ürün stoğunu sıfırla ki başka yerde satılmasın
                    $auction->product->update(['stock_quantity' => 0]);

                    $this->line("İhale #{$auction->id} kapatıldı. Kazanan: {$winner->name} (₺{$highestBid->amount})");
                } else {
                    // TEKLİF YOK: İhale kimseye satılmadan kapandı
                    $this->line("İhale #{$auction->id} kapatıldı. (Hiç teklif alınmadı)");
                }

                DB::commit();
                $closedCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("İhale #{$auction->id} kapatılırken hata oluştu: " . $e->getMessage());
                $this->error("İhale #{$auction->id} kapatılırken hata: " . $e->getMessage());
            }
        }

        $this->info("İşlem tamamlandı! Toplam {$closedCount} ihale başarıyla kapatıldı.");
    }
}