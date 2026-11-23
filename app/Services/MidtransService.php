<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Alamat webhook per transaksi (opsional). Kalau MIDTRANS_NOTIFICATION_URL diisi, Midtrans
        // memakainya dan mengabaikan Payment Notification URL di dashboard. Berguna saat testing
        // dengan ngrok. Kosongkan di produksi supaya memakai pengaturan dashboard.
        if (config('midtrans.notification_url')) {
            Config::$overrideNotifUrl = config('midtrans.notification_url');
        }
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

    /**
     * Tanyakan status transaksi langsung ke Midtrans (memakai Server Key kita), sebagai cadangan kalau
     * webhook terlambat atau tidak sampai. Mengembalikan null kalau transaksi belum ada di Midtrans
     * (pelanggan belum memilih metode bayar) atau Midtrans sedang tidak bisa dihubungi.
     */
    public function fetchStatus(string $orderId): ?array
    {
        try {
            return (array) Transaction::status($orderId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Nominal dari Midtrans harus sama dengan total order di database. */
    public function amountMatches(Order $order, array $midtransResult): bool
    {
        if (! isset($midtransResult['gross_amount'])) {
            return false;
        }

        return (int) round((float) $midtransResult['gross_amount']) === (int) round((float) $order->total_price);
    }

    /**
     * Terapkan status transaksi dari Midtrans ke order. Dipakai bersama oleh webhook dan pengecekan
     * langsung, supaya perilakunya sama persis. Idempoten: aman dipanggil berulang.
     */
    public function applyStatus(Order $order, ?string $status, ?string $fraudStatus = null): void
    {
        if (in_array($status, ['capture', 'settlement']) && $fraudStatus !== 'deny') {
            if ($order->payment_status !== 'success') {
                $order->update([
                    'payment_status' => 'success',
                    'paid_at' => now(),
                ]);

                $order->statusLogs()->create([
                    'changed_by' => null,
                    'status' => 'pembayaran_berhasil',
                    'changed_at' => now(),
                ]);

                // Order yang sudah dibatalkan Admin sebelum lunas tidak boleh muncul di dapur.
                if (! $order->cancelled_at) {
                    event(new OrderCreated($order));
                }
            }
        } elseif ($order->payment_status === 'menunggu_pembayaran') {
            // Status gagal/kedaluwarsa hanya boleh mengubah order yang masih menunggu.
            if ($status === 'expire') {
                $order->update(['payment_status' => 'expired']);
            } elseif (in_array($status, ['deny', 'cancel'])) {
                $order->update(['payment_status' => 'failed']);
            }
        }
    }
}
