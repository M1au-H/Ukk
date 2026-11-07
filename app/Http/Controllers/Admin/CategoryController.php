<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::withCount('menus')->orderBy('sort_order')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        abort_if(
            $category->menus()->exists(),
            422,
            'Kategori ini masih punya menu. Pindahkan atau hapus menunya dulu.'
        );

        $category->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
