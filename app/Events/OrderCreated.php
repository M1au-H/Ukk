<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * ShouldBroadcastNow (bukan ShouldBroadcast) sengaja dipakai supaya event
 * langsung terkirim saat itu juga, tanpa perlu jalankan queue worker terpisah
 * (php artisan queue:work). Untuk solo dev, ini menyederhanakan proses jadi
 * cukup 2 server yang perlu jalan: `php artisan serve` + `php artisan reverb:start`.
 */
class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('kitchen')];
    }

    public function broadcastAs(): string
    {
        return 'OrderCreated';
    }

    public function broadcastWith(): array
    {
        $this->order->loadMissing(['table', 'items.options']);

        return [
            'order' => [
                'id' => $this->order->id,
                'table_number' => $this->order->table->table_number,
                'customer_name' => $this->order->customer_name,
                'kitchen_status' => $this->order->kitchen_status,
                'total_price' => $this->order->total_price,
                'created_at' => $this->order->created_at,
                'items' => $this->order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'menu_name' => $item->menu_name_snapshot,
                    'qty' => $item->qty,
                    'note' => $item->note,
                    'options' => $item->options->map(fn ($opt) => [
                        'label' => $opt->option_label_snapshot,
                        'value' => $opt->value_snapshot,
                    ]),
                ]),
            ],
        ];
    }
}
