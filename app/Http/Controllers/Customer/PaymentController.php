<?php

namespace App\Http\Controllers\Customer;

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
            $order->update([
                'payment_status' => 'success',
                'paid_at' => now(),
            ]);

            // TODO Milestone 4: broadcast(new OrderCreated($order)) ke channel private-kitchen.
            // Baru di titik inilah Kitchen boleh tahu order ini ada.
        } elseif ($status === 'expire') {
            $order->update(['payment_status' => 'expired']);
        } elseif (in_array($status, ['deny', 'cancel'])) {
            $order->update(['payment_status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }
}
