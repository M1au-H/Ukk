<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with(['category', 'optionGroups.values']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return $query->get();
    }

    public function show(Menu $menu)
    {
        return $menu->load(['category', 'optionGroups.values']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
            'supports_spicy_level' => ['boolean'],
        ]);

        $menu = Menu::create($data);

        return response()->json($menu, 201);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_available' => ['boolean'],
            'supports_spicy_level' => ['boolean'],
        ]);

        $menu->update($data);

        return response()->json($menu);
    }

    /**
     * Quick toggle stok habis/tersedia dari list menu di Admin Dashboard.
     * Broadcasting realtime ke Customer App ditambahkan di Milestone 4.
     */
    public function toggleAvailability(Menu $menu)
    {
        $menu->update(['is_available' => ! $menu->is_available]);

        return response()->json($menu);
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return response()->json(['message' => 'Menu dihapus.']);
    }
}
