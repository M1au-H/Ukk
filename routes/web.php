<?php

use Illuminate\Support\Facades\Route;

/*
 * Customer App (Vue) sudah di-build ke public/customer, lalu dilayani Laravel dari sini.
 * Jadi cukup satu server (php artisan serve) untuk API sekaligus halaman pelanggan.
 * Semua alamat halaman Vue harus membalas index.html yang sama; yang menentukan
 * halaman mana yang tampil adalah router di sisi Vue.
 */
$customerApp = function () {
    $file = public_path('customer/index.html');

    abort_unless(
        is_file($file),
        503,
        'Customer App belum di-build. Jalankan "npm run build:laravel" di folder restaurant-customer-app.'
    );

    // no-cache supaya hasil build terbaru langsung terbaca (file di /customer/assets sudah ber-hash).
    return response()->file($file, ['Cache-Control' => 'no-cache']);
};

Route::get('/', $customerApp);
Route::get('/menu/{tableToken}', $customerApp);
Route::get('/cart', $customerApp);
Route::get('/status', $customerApp);

// Route dummy: project ini API-only, tidak ada halaman login berbasis web.
// Route ini WAJIB ada (walau tidak pernah benar-benar diakses user) supaya
// Laravel tidak error saat mencoba generate URL ke route('login') ketika
// ada request API tanpa token. Middleware auth:sanctum butuh route ini "ada",
// biar proses redirect-nya bisa dibatalkan dengan benar oleh bootstrap/app.php
// dan diganti jadi response JSON 401.
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');