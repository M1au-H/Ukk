<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderCancelled;
use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'table_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', 'in:menunggu_pembayaran,success,failed,expired'],
            'kitchen_status' => ['nullable', 'in:diterima_dapur,diproses,siap_disajikan,selesai'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'cancelled' => ['nullable', 'in:0,1'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Order::with(['table:id,table_number', 'items:id,order_id,menu_name_snapshot,qty'])->latest();

        if ($request->filled('table_id')) {
            $query->where('table_id', $request->table_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('kitchen_status')) {
            $query->where('kitchen_status', $request->kitchen_status);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('cancelled')) {
            $request->cancelled === '1'
                ? $query->whereNotNull('cancelled_at')
                : $query->whereNull('cancelled_at');
        }
        if ($request->filled('q')) {
            $query->where('customer_name', 'like', '%'.$request->q.'%');
        }

        return $query->paginate(15)->withQueryString();
    }

    public function show(Order $order)
    {
        return $this->detail($order);
    }

    /**
     * Batalkan order. Hanya menandai dibatalkan (riwayat tetap ada). Pengembalian dana,
     * kalau diperlukan, diurus manual lewat dashboard Midtrans.
     */
    public function cancel(Request $request, Order $order)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:200']]);

        abort_if($order->cancelled_at, 422, 'Order ini sudah dibatalkan.');
        abort_if(
            ! in_array($order->payment_status, ['menunggu_pembayaran', 'success']),
            422,
            'Order dengan pembayaran gagal atau kedaluwarsa tidak perlu dibatalkan.'
        );
        abort_if(
            $order->payment_status === 'success' && $order->kitchen_status === 'selesai',
            422,
            'Order yang sudah selesai tidak bisa dibatalkan.'
        );

        $order->update([
            'cancelled_at' => now(),
            'cancel_reason' => $data['reason'] ?? null,
            'cancelled_by' => $request->user()->id,
        ]);

        $order->statusLogs()->create([
            'changed_by' => $request->user()->id,
            'status' => 'dibatalkan',
            'changed_at' => now(),
        ]);

        // Customer: kartu pesanannya berubah jadi "dibatalkan".
        event(new OrderStatusUpdated($order));

        // Kitchen/Admin: hanya relevan kalau order ini sempat tampil di dapur (sudah lunas).
        if ($order->payment_status === 'success') {
            event(new OrderCancelled($order));
        }

        return response()->json($this->detail($order->fresh()));
    }

    private function detail(Order $order): Order
    {
        return $order->load([
            'table',
            'items.options',
            'statusLogs' => fn ($q) => $q->orderBy('changed_at')->with('changedBy:id,name'),
        ]);
    }
}
