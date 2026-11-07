<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentWebhookLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function callback(Request $request)
    {
        $payload = $request->all();
        $isValid = $this->midtransService->isSignatureValid($payload);

        $order = Order::find($payload['order_id'] ?? null);

        // Simpan SEMUA callback yang masuk (valid maupun tidak) untuk audit/debug.
        PaymentWebhookLog::create([
            'order_id' => $order?->id,
            'raw_payload' => $payload,
            'signature_valid' => $isValid,
            'received_at' => now(),
        ]);

        if (! $isValid) {
            // Signature tidak cocok = kemungkinan fake webhook. JANGAN proses lebih lanjut.
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $status = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (in_array($status, ['capture', 'settlement']) && $fraudStatus !== 'deny') {
            // Idempoten: Midtrans bisa mengirim callback yang sama lebih dari sekali.
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
            // Callback gagal/kedaluwarsa hanya boleh mengubah order yang masih menunggu.
            if ($status === 'expire') {
                $order->update(['payment_status' => 'expired']);
            } elseif (in_array($status, ['deny', 'cancel'])) {
                $order->update(['payment_status' => 'failed']);
            }
        }

        return response()->json(['message' => 'OK']);
    }
}
