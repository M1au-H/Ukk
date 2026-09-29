<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createSnapToken(Order $order): string
    {
        $order->update(['midtrans_order_id' => $order->id]);

        $params = [
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->menu_id,
                'price' => (int) round($item->subtotal / $item->qty),
                'quantity' => $item->qty,
                'name' => substr($item->menu_name_snapshot, 0, 50),
            ])->toArray(),
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Verifikasi signature_key dari webhook Midtrans SEBELUM mempercayai payload apa pun.
     * Formula resmi Midtrans: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function isSignatureValid(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.config('midtrans.server_key'));

        return hash_equals($expected, (string) $signatureKey);
    }
}
