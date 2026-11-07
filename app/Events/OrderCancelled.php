<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Memberi tahu layar Kitchen (dan Admin) bahwa sebuah order yang sudah lunas dibatalkan. */
class OrderCancelled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('kitchen')];
    }

    public function broadcastAs(): string
    {
        return 'OrderCancelled';
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing('table');

        return [
            'order_id' => $this->order->id,
            'table_number' => $this->order->table?->table_number,
        ];
    }
}
