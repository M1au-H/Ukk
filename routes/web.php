<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 * Aplikasi Vue hasil build dilayani langsung oleh Laravel dari folder public:
 *   - Customer App           -> public/customer  (alamat /, /menu/{token}, /cart, /status)
 *   - Admin + Kitchen App    -> public/panel     (alamat /panel/...)
 * Jadi cukup satu server (php artisan serve) untuk API dan semua halaman.
 * Semua alamat halaman membalas index.html yang sama; yang menentukan halaman mana yang
 * tampil adalah router di sisi Vue.
 */
$spa = function (string $folder, string $namaAplikasi) {
    return function () use ($folder, $namaAplikasi) {
        $file = public_path($folder.'/index.html');

        abort_unless(
            is_file($file),
            503,
            'Aplikasi belum di-build. Jalankan "npm run build:laravel" di folder '.$namaAplikasi.'.'
        );

        // no-cache supaya hasil build terbaru langsung terbaca (file di /assets sudah ber-hash).
        return response()->file($file, ['Cache-Control' => 'no-cache']);
    };
};

$customerApp = $spa('customer', 'restaurant-customer-app');
$panelApp = $spa('panel', 'restaurant-customer-app');

Route::get('/', $customerApp);
Route::get('/menu/{tableToken}', $customerApp);
Route::get('/cart', $customerApp);
Route::get('/status', $customerApp);

Route::get('/panel/{any?}', $panelApp)->where('any', '.*');

// Jaring pengaman: kalau karena sebab apa pun alamat di bawah /panel (misal /panel/orders saat
// halaman di-refresh) tidak ditangkap rute di atas, tetap layani Admin App. Alamat lain tetap 404.
Route::fallback(function (Request $request) use ($panelApp) {
    if ($request->is('panel') || $request->is('panel/*')) {
        return $panelApp();
    }

    abort(404);
});

// Route dummy: project ini API-only, tidak ada halaman login berbasis web.
// Route ini WAJIB ada (walau tidak pernah benar-benar diakses user) supaya
// Laravel tidak error saat mencoba generate URL ke route('login') ketika
// ada request API tanpa token. Middleware auth:sanctum butuh route ini "ada",
// biar proses redirect-nya bisa dibatalkan dengan benar oleh bootstrap/app.php
// dan diganti jadi response JSON 401.
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');  