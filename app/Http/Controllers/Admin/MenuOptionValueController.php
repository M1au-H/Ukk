<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuOptionGroup;
use App\Models\MenuOptionValue;
use Illuminate\Http\Request;

class MenuOptionValueController extends Controller
{
    public function store(Request $request, MenuOptionGroup $optionGroup)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'extra_price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $value = $optionGroup->values()->create($data);

        return response()->json($value, 201);
    }

    public function update(Request $request, MenuOptionValue $value)
    {
        $data = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'extra_price' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $value->update($data);

        return response()->json($value);
    }

    public function destroy(MenuOptionValue $value)
    {
        $value->delete();

        return response()->json(['message' => 'Pilihan opsi dihapus.']);
    }
}
