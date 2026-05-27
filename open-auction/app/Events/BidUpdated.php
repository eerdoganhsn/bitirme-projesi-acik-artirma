<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Anında yayınlamak için Now kullanıyoruz
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $auctionId;
    public $newPrice;
    public $bidderName;

    /**
     * Olay tetiklendiğinde Vue tarafına gönderilecek veriler
     */
    public function __construct($auctionId, $newPrice, $bidderName)
    {
        $this->auctionId = $auctionId;
        $this->newPrice = $newPrice;
        $this->bidderName = $bidderName;
    }

    /**
     * Hangi kanaldan yayın yapılacağı (Örn: auction.5)
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('auction.' . $this->auctionId),
        ];
    }

    /**
     * Vue tarafında dinleyeceğimiz isimlendirme
     */
    public function broadcastAs(): string
    {
        return 'bid.placed';
    }
}