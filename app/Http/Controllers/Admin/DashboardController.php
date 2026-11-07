<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $start = now()->startOfDay();

        // Pesanan "hari ini" = yang dibayar hari ini dan tidak dibatalkan.
        $paidToday = fn () => Order::where('payment_status', 'success')
            ->whereNull('cancelled_at')
            ->where('paid_at', '>=', $start);

        $topMenus = OrderItem::query()
            ->select('menu_name_snapshot', DB::raw('SUM(qty) as total_qty'))
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'success')
                ->whereNull('cancelled_at')
                ->where('paid_at', '>=', $start))
            ->groupBy('menu_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'orders_today' => $paidToday()->count(),
            'revenue_today' => (float) $paidToday()->sum('total_price'),
            'active_orders' => Order::paidAndActive()->count(),
            'pending_payment' => Order::where('payment_status', 'menunggu_pembayaran')
                ->whereNull('cancelled_at')
                ->count(),
            'top_menus' => $topMenus->map(fn ($row) => [
                'name' => $row->menu_name_snapshot,
                'qty' => (int) $row->total_qty,
            ])->values(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
