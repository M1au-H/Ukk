<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuOptionGroup;
use Illuminate\Http\Request;

class MenuOptionGroupController extends Controller
{
    public function index(Menu $menu)
    {
        return $menu->optionGroups()->with('values')->get();
    }

    public function store(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'selection_type' => ['required', 'in:single,multiple'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $group = $menu->optionGroups()->create($data);

        return response()->json($group, 201);
    }

    public function update(Request $request, MenuOptionGroup $optionGroup)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'selection_type' => ['sometimes', 'required', 'in:single,multiple'],
            'is_required' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $optionGroup->update($data);

        return response()->json($optionGroup);
    }

    public function destroy(MenuOptionGroup $optionGroup)
    {
        $optionGroup->delete();

        return response()->json(['message' => 'Grup opsi dihapus.']);
    }
}
