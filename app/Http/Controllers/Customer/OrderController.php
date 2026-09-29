<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\OrderPricingService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderPricingService $pricingService,
        protected MidtransService $midtransService,
    ) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_token' => ['required', 'string'],
            'session_token' => ['required', 'string', 'max:64'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.option_value_ids' => ['nullable', 'array'],
            'items.*.option_value_ids.*' => ['integer'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $table = DiningTable::where('table_token', $data['table_token'])
            ->where('is_active', true)
            ->firstOrFail();

        $order = $this->pricingService->createOrder(
            $table,
            $data['session_token'],
            $data['customer_name'],
            $data['items'],
        );

        $snapToken = $this->midtransService->createSnapToken($order);
        $order->update(['snap_token' => $snapToken]);

        return response()->json([
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'snap_token' => $snapToken,
        ], 201);
    }

    public function index(Request $request)
    {
        $request->validate(['session_token' => ['required', 'string']]);

        return Order::where('session_token', $request->session_token)
            ->with('items.options')
            ->latest()
            ->get();
    }

    public function show(Request $request, Order $order)
    {
        $request->validate(['session_token' => ['required', 'string']]);

        abort_unless($order->session_token === $request->session_token, 403);

        return $order->load('items.options');
    }
}
