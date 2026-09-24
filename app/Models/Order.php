<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'table_id', 'session_token', 'customer_name', 'payment_status',
        'kitchen_status', 'total_price', 'midtrans_order_id', 'snap_token', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function webhookLogs()
    {
        return $this->hasMany(PaymentWebhookLog::class);
    }

    public function scopePaidAndActive($query)
    {
        return $query->where('payment_status', 'success')->where('kitchen_status', '!=', 'selesai');
    }
}
