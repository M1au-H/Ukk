# Milestone 2 — Admin CRUD Dasar

## Langkah 1 — Aktifkan Routing API

Laravel 11+ (termasuk versi 13 yang kamu pakai) tidak otomatis punya `routes/api.php` di
project baru. Jalankan ini dulu di CMD (posisi di folder `restaurant-api`):

```
php artisan install:api
```

Kalau ditanya konfirmasi overwrite/publish (misal soal migration Sanctum), jawab **yes**.
Ini akan:
- Membuat file `routes/api.php` (kalau belum ada)
- Mendaftarkan file itu otomatis ke `bootstrap/app.php`
- Publish migration `create_personal_access_tokens_table` (tabel token Sanctum)

Setelah itu jalankan:
```
php artisan migrate
```
(cukup `migrate` biasa, bukan `fresh`, supaya data seeder yang sudah ada tidak hilang —
ini cuma menambah 1 tabel baru untuk token Sanctum)

## Langkah 2 — Copy File dari Paket Ini

- `app/Http/Middleware/EnsureUserHasRole.php` → masuk ke `app/Http/Middleware/`
- `app/Http/Controllers/AuthController.php` → masuk ke `app/Http/Controllers/`
- `app/Http/Controllers/Admin/*.php` (6 file) → masuk ke `app/Http/Controllers/Admin/`
  (folder `Admin` mungkin belum ada, buat dulu)
- `routes/api.php` → **timpa** file yang baru dibuat `install:api` tadi

## Langkah 3 — Daftarkan Middleware `role`

Buka `bootstrap/app.php`, cari bagian `->withMiddleware(function (Middleware $middleware) {`
lalu tambahkan baris `alias` di dalamnya. Contoh setelah diedit:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

Kalau closure itu tadinya kosong (`function (Middleware $middleware) {})`), tinggal isi
persis seperti contoh di atas.

## Langkah 4 — Testing Manual via Postman (WAJIB sebelum lanjut)

Ini checkpoint penting sesuai rencana kita — pastikan API-nya benar dulu sebelum sentuh
frontend.

1. **Login sebagai Admin:**
   `POST http://localhost:8000/api/auth/login`
   Body (JSON):
   ```json
   { "email": "admin@restoran.test", "password": "password" }
   ```
   Response harusnya berisi `token`. **Copy token itu.**

   > Catatan: kalau server belum jalan, buka CMD baru di folder project, jalankan
   > `php artisan serve` dulu supaya bisa diakses di `localhost:8000`.

2. **Test endpoint Admin pakai token itu:**
   `GET http://localhost:8000/api/admin/categories`
   Di tab **Authorization** Postman → pilih **Bearer Token** → paste token dari langkah 1.

   Harusnya muncul 2 kategori (Main Course, Beverage) dari seeder.

3. **Test tanpa token / pakai token asal-asalan:**
   Ulangi request yang sama tapi hapus tokennya → harusnya dapat `401 Unauthorized`.
   Ini membuktikan endpoint admin sudah terlindungi.

4. **Test dengan akun Kitchen (bukan Admin):**
   Login pakai `kitchen@restoran.test` / `password`, pakai token itu untuk akses
   `/api/admin/categories` → harusnya dapat `403 Forbidden` (bukti middleware `role:admin`
   jalan dengan benar, Kitchen tidak bisa akses endpoint Admin).

5. **Test CRUD lain:**
   - `POST /api/admin/menus/{id}/option-groups` untuk tambah grup opsi baru ke menu
   - `PATCH /api/admin/menus/{id}/toggle-availability` untuk toggle stok
   - `POST /api/admin/tables/{id}/regenerate-token` untuk regenerate QR

## Kalau Semua Test di Atas Lolos

Milestone 2 selesai. Lanjut ke **Milestone 3 — Customer Flow Sampai Bayar** (endpoint
resolve token meja, list menu untuk customer, `OrderPricingService`, dan integrasi
Midtrans Sandbox + webhook). Ini bagian paling kritis di seluruh project, jadi kabari
saya kalau sudah siap.
