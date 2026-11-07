<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

/**
 * Foto menu disimpan langsung di public/uploads/menu (bukan storage/app/public),
 * supaya tidak perlu "php artisan storage:link" yang sering bermasalah di Windows.
 * Yang disimpan di database hanya path relatif, misal /uploads/menu/menu-1-1760000000.jpg
 */
class MenuImageController extends Controller
{
    public function store(Request $request, Menu $menu)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $file = $request->file('image');
        $dir = public_path('uploads/menu');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Ekstensi diambil dari isi file, bukan dari nama file kiriman pengguna.
        $name = 'menu-'.$menu->id.'-'.time().'.'.($file->guessExtension() ?: 'jpg');
        $file->move($dir, $name);

        $this->removeFile($menu->image_url);
        $menu->update(['image_url' => '/uploads/menu/'.$name]);

        return response()->json($menu->fresh());
    }

    public function destroy(Menu $menu)
    {
        $this->removeFile($menu->image_url);
        $menu->update(['image_url' => null]);

        return response()->json($menu->fresh());
    }

    private function removeFile(?string $url): void
    {
        if ($url && str_starts_with($url, '/uploads/menu/')) {
            $path = public_path(ltrim($url, '/'));
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
