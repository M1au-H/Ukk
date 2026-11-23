<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentWebhookLog;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $this->midtransService->applyStatus(
            $order,
            $payload['transaction_status'] ?? null,
            $payload['fraud_status'] ?? null,
        );

        return response()->json(['message' => 'OK']);
    }

    /**
     * Cadangan kalau webhook terlambat atau tidak sampai: Customer App meminta server menanyakan status
     * transaksi langsung ke Midtrans. Yang dipercaya adalah jawaban Midtrans (lewat Server Key kita),
     * bukan kiriman dari pelanggan, jadi tidak bisa dipalsukan.
     */
    public function sync(Request $request, Order $order)
    {
        $request->validate(['session_token' => ['required', 'string']]);

        abort_unless($order->session_token === $request->session_token, 403);

        // Cek ke Midtrans paling sering sekali tiap 8 detik per order, walau aplikasi memanggil lebih sering.
        if ($order->payment_status === 'menunggu_pembayaran' && Cache::add('sync-payment:'.$order->id, 1, 8)) {
            $result = $this->midtransService->fetchStatus($order->midtrans_order_id ?: $order->id);

            if ($result && $this->midtransService->amountMatches($order, $result)) {
                $this->midtransService->applyStatus(
                    $order,
                    $result['transaction_status'] ?? null,
                    $result['fraud_status'] ?? null,
                );
            }
        }

        return $order->fresh()->load('items.options');
    }
}
