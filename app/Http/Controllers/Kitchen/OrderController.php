<?php

namespace App\Http\Controllers\Kitchen;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List order aktif (payment sukses, belum selesai), dikelompokkan per meja.
     * Response: { "14": [order, order, ...], "5": [order, ...] }
     */
    public function index()
    {
        $orders = Order::paidAndActive()
            ->with(['table', 'items.options'])
            ->orderBy('created_at')
            ->get();

        return $orders->groupBy(fn ($order) => $order->table->table_number);
    }

    public function show(Order $order)
    {
        abort_unless($order->payment_status === 'success', 404);

        return $order->load(['table', 'items.options']);
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($order->payment_status === 'success', 422, 'Order ini belum lunas.');

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
}
