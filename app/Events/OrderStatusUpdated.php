<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Channel PUBLIK yang dinamai pakai session_token (lihat catatan di dokumen konteks:
 * customer tidak punya akun, jadi tidak bisa memakai channel privat).
 * Dipakai untuk perubahan status dapur DAN pembatalan oleh Admin (cancelled = true).
 */
class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new Channel('order.'.$this->order->session_token)];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'kitchen_status' => $this->order->kitchen_status,
            'cancelled' => $this->order->cancelled_at !== null,
            'cancel_reason' => $this->order->cancel_reason,
        ];
    }
}
