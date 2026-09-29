<?php

namespace App\Services;

use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\MenuOptionValue;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPricingService
{
    /**
     * Hitung ulang harga di server dari menu_id + option_value_ids yang dikirim client.
     * TIDAK PERNAH percaya harga/price yang dikirim dari request body.
     *
     * @param  array  $items  [['menu_id' => int, 'qty' => int, 'option_value_ids' => int[], 'note' => ?string], ...]
     */
    public function createOrder(DiningTable $table, string $sessionToken, string $customerName, array $items): Order
    {
        return DB::transaction(function () use ($table, $sessionToken, $customerName, $items) {
            $order = Order::create([
                'table_id' => $table->id,
                'session_token' => $sessionToken,
                'customer_name' => $customerName,
                'payment_status' => 'menunggu_pembayaran',
                'kitchen_status' => 'diterima_dapur',
                'total_price' => 0,
            ]);

            $total = 0;

            foreach ($items as $itemData) {
                $menu = Menu::where('is_active', true)
                    ->where('is_available', true)
                    ->find($itemData['menu_id']);

                if (! $menu) {
                    throw ValidationException::withMessages([
                        'items' => ["Menu dengan id {$itemData['menu_id']} tidak tersedia."],
                    ]);
                }

                $qty = max(1, (int) $itemData['qty']);
                $optionValueIds = $itemData['option_value_ids'] ?? [];

                // Validasi: semua option_value_id yang dikirim harus benar-benar
                // milik option group yang menempel di menu ini (bukan menu lain / rekayasa client).
                $selectedValues = MenuOptionValue::whereIn('id', $optionValueIds)
                    ->whereHas('optionGroup', fn ($q) => $q->where('menu_id', $menu->id))
                    ->with('optionGroup')
                    ->get();

                if (count($selectedValues) !== count($optionValueIds)) {
                    throw ValidationException::withMessages([
                        'items' => ['Salah satu opsi yang dipilih tidak valid untuk menu ini.'],
                    ]);
                }

                // Validasi: grup opsi wajib (is_required) harus ada 1 value terpilih.
                $requiredGroupIds = $menu->optionGroups()->where('is_required', true)->pluck('id');
                foreach ($requiredGroupIds as $groupId) {
                    $hasSelection = $selectedValues->contains(fn ($v) => $v->option_group_id === $groupId);
                    if (! $hasSelection) {
                        throw ValidationException::withMessages([
                            'items' => ["Menu \"{$menu->name}\" punya opsi wajib yang belum dipilih."],
                        ]);
                    }
                }

                $extraPriceTotal = $selectedValues->sum('extra_price');
                $unitPrice = $menu->price + $extraPriceTotal;
                $subtotal = $unitPrice * $qty;

                $orderItem = $order->items()->create([
                    'menu_id' => $menu->id,
                    'menu_name_snapshot' => $menu->name,
                    'price_snapshot' => $menu->price,
                    'qty' => $qty,
                    'note' => $itemData['note'] ?? null,
                    'subtotal' => $subtotal,
                ]);

                foreach ($selectedValues as $value) {
                    $orderItem->options()->create([
                        'option_label_snapshot' => $value->optionGroup->name,
                        'value_snapshot' => $value->label,
                        'extra_price_snapshot' => $value->extra_price,
                    ]);
                }

                $total += $subtotal;
            }

            $order->update(['total_price' => $total]);

            return $order->fresh('items.options');
        });
    }
}
