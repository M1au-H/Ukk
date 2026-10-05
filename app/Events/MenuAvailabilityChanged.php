<?php

namespace App\Events;

use App\Models\Menu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuAvailabilityChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Menu $menu) {}

    public function broadcastOn(): array
    {
        return [new Channel('menu-availability')];
    }

    public function broadcastAs(): string
    {
        return 'MenuAvailabilityChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'is_available' => $this->menu->is_available,
        ];
    }
}
