<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Channel PUBLIK (bukan PrivateChannel) yang dinamai pakai session_token.
 * Customer tidak punya akun/login, jadi tidak bisa dilewatkan lewat mekanisme
 * private-channel-auth Sanctum yang normal. Karena session_token sendiri
 * sudah berupa string acak panjang yang tidak bisa ditebak (mirip table_token),
 * ini "aman secukupnya" untuk kebutuhan project ini: orang lain harus tahu
 * persis session_token itu untuk bisa subscribe ke channel yang sama.
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
        ];
    }
}
