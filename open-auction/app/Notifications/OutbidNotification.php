<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

// ShouldBroadcast arayüzünü ekliyoruz ki anlık yayınlanabilsin
class OutbidNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $productName;
    public $productId;
    public $newPrice;

    public function __construct($productName, $productId, $newPrice)
    {
        $this->productName = $productName;
        $this->productId = $productId;
        $this->newPrice = $newPrice;
    }

    // Bildirimin hem veritabanına kaydedilmesini hem de anlık yayınlanmasını söylüyoruz
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    // 1. Veritabanına Kaydedilecek Veri Formatı
    public function toArray($notifiable): array
    {
        return [
            'type' => 'outbid',
            'product_id' => $this->productId,
            'message' => "{$this->productName} ihalemizde teklifiniz geçildi! Yeni lider fiyatı: ₺{$this->newPrice}",
        ];
    }

    // 2. Canlı Yayından (WebSockets) Fırlatılacak Veri Formatı
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'outbid',
            'product_id' => $this->productId,
            'message' => "{$this->productName} ihalemizde teklifiniz geçildi! Yeni lider fiyatı: ₺{$this->newPrice}",
        ]);
    }
}