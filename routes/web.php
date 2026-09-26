<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route dummy — project ini API-only, tidak ada halaman login berbasis web.
// Route ini WAJIB ada (walau tidak pernah benar-benar diakses user) supaya
// Laravel tidak error saat mencoba generate URL ke route('login') ketika
// ada request API tanpa token. Middleware auth:sanctum butuh route ini "ada",
// biar proses redirect-nya bisa dibatalkan dengan benar oleh bootstrap/app.php
// dan diganti jadi response JSON 401.
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');