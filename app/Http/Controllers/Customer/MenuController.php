<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function resolveTable(string $tableToken)
    {
        $table = DiningTable::where('table_token', $tableToken)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
        ]);
    }

    public function categories()
    {
        return Category::orderBy('sort_order')->get();
    }

    public function menus(Request $request)
    {
        $query = Menu::where('is_active', true)->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return $query->get();
    }

    public function showMenu(Menu $menu)
    {
        abort_unless($menu->is_active, 404);

        return $menu->load(['category', 'optionGroups.values']);
    }
}
