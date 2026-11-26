<?php

namespace App\Http\Controllers\Kitchen;

use App\Events\OrderCancelled;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List order aktif (payment sukses, belum selesai, tidak dibatalkan), dikelompokkan per meja.
     * Response: { "14": [order, order, ...], "5": [order, ...] }
     */
    public function index()
    {
        $orders = Order::paidAndActive()
            ->with([
                'table',
                'items.options',
                'statusLogs' => fn ($q) => $q->orderBy('changed_at')->with('changedBy:id,name'),
            ])
            ->orderBy('created_at')
            ->get();

        return $orders->groupBy(fn ($order) => $order->table->table_number);
    }

    /**
     * Order yang sudah selesai (paling baru di atas, maksimal 100).
     * Bisa difilter berdasarkan tanggal pengerjaan (date=YYYY-MM-DD atau date=all).
     */
    public function history(Request $request)
    {
        $query = Order::where('payment_status', 'success')
            ->where('kitchen_status', 'selesai')
            ->whereNull('cancelled_at')
            ->with([
                'table',
                'items.options',
                'statusLogs' => fn ($q) => $q->orderBy('changed_at')->with('changedBy:id,name'),
            ]);

        if ($request->filled('date')) {
            if ($request->date !== 'all') {
                $query->whereDate('updated_at', $request->date);
            }
        } else {
            $query->whereDate('updated_at', today());
        }

        return $query->latest('updated_at')
            ->limit(100)
            ->get();
    }

    public function show(Order $order)
    {
        abort_unless($order->payment_status === 'success' && ! $order->cancelled_at, 404);

        return $order->load(['table', 'items.options']);
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($order->payment_status === 'success', 422, 'Order ini belum lunas.');
        abort_if($order->cancelled_at, 422, 'Order ini sudah dibatalkan.');

        $data = $request->validate([
            'kitchen_status' => ['required', 'in:diterima_dapur,diproses,siap_disajikan,selesai'],
        ]);

        $order->update(['kitchen_status' => $data['kitchen_status']]);

        $order->statusLogs()->create([
            'changed_by' => $request->user()->id,
            'status' => $data['kitchen_status'],
            'changed_at' => now(),
        ]);

        event(new OrderStatusUpdated($order));

        return response()->json($order);
    }

    /**
     * Kembalikan / batalkan order dari dapur dengan alasan (misal: bahan habis).
     */
    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:200'],
        ]);

        abort_unless($order->payment_status === 'success', 422, 'Order ini belum lunas.');
        abort_if($order->cancelled_at, 422, 'Order ini sudah dibatalkan.');
        abort_if(
            $order->kitchen_status === 'selesai',
            422,
            'Order yang sudah selesai tidak bisa dikembalikan.'
        );

        $order->update([
            'cancelled_at' => now(),
            'cancel_reason' => $data['reason'],
            'cancelled_by' => $request->user()->id,
        ]);

        $order->statusLogs()->create([
            'changed_by' => $request->user()->id,
            'status' => 'dibatalkan',
            'changed_at' => now(),
        ]);

        // Customer: realtime mendapatkan pemberitahuan pembatalan dan alasan
        event(new OrderStatusUpdated($order));

        // Kitchen & Admin: kartu pesanan otomatis hilang dari antrean
        event(new OrderCancelled($order));

        return response()->json([
            'message' => 'Pesanan berhasil dikembalikan ke pelanggan.',
            'order' => $order,
        ]);
    }
}
