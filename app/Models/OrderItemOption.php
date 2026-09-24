<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    protected $fillable = ['order_item_id', 'option_label_snapshot', 'value_snapshot', 'extra_price_snapshot'];

    protected function casts(): array
    {
        return ['extra_price_snapshot' => 'decimal:2'];
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
