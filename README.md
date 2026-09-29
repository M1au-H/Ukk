# Milestone 3 — Customer Flow Sampai Bayar

Ini bagian paling kritis: order → hitung ulang harga di server → Midtrans Snap →
webhook → payment_status berubah jadi `success`.

## Langkah 1 — Daftar Akun Midtrans Sandbox

1. Buka https://dashboard.sandbox.midtrans.com/register (gratis, khusus testing)
2. Setelah login, buka menu **Settings → Access Keys**
3. Catat **Server Key** dan **Client Key** (masih di mode Sandbox, bukan Production)

## Langkah 2 — Install SDK Midtrans

Di CMD, folder `restaurant-api`:
```
composer require midtrans/midtrans-php
```

## Langkah 3 — Update `.env`

Tambahkan baris ini di `.env`:
```
MIDTRANS_SERVER_KEY=isi_server_key_kamu
MIDTRANS_CLIENT_KEY=isi_client_key_kamu
MIDTRANS_IS_PRODUCTION=false
```

## Langkah 4 — Copy File dari Paket Ini

- `config/midtrans.php` → masuk ke `config/`
- `app/Services/MidtransService.php` dan `app/Services/OrderPricingService.php` →
  masuk ke `app/Services/` (folder ini belum ada, buat dulu)
- `app/Http/Controllers/Customer/*.php` (3 file) → masuk ke
  `app/Http/Controllers/Customer/` (folder baru, buat dulu)
- `routes/api.php` → **timpa** file yang sudah ada (ini gabungan rute Admin dari
  Milestone 2 + rute Customer yang baru, jadi aman ditimpa penuh)

Setelah semua file masuk, jalankan:
```
composer dump-autoload
```

## Langkah 5 — Testing via Postman (checkpoint WAJIB)

### A. Test alur normal (tanpa bayar sungguhan dulu)

1. **Lihat menu:**
   `GET http://localhost:8000/api/menus`
   → catat salah satu `id` menu dan `id` dari `option_value` di dalamnya (buka
   `GET /api/menus/{id}` untuk lihat detail option groups & values-nya)

2. **Resolve meja:**
   `GET http://localhost:8000/api/menu/resolve/{table_token}`
   → ambil salah satu `table_token` dari tabel `tables` di phpMyAdmin

3. **Buat order:**
   `POST http://localhost:8000/api/orders`
   Body (JSON) — sesuaikan `menu_id`/`option_value_ids` dengan id asli dari langkah 1:
   ```json
   {
     "table_token": "isi_token_meja",
     "session_token": "test-session-123",
     "customer_name": "Budi",
     "items": [
       {
         "menu_id": 1,
         "qty": 2,
         "option_value_ids": [3, 5],
         "note": "tidak pakai bawang"
       }
     ]
   }
   ```
   Response harusnya berisi `order_id`, `total_price` (sudah dihitung ulang server,
   BUKAN dari input kamu), dan `snap_token`.

4. **Cek order tersimpan dengan status `menunggu_pembayaran`:**
   `GET http://localhost:8000/api/orders?session_token=test-session-123`

### B. Test validasi keamanan (WAJIB dicoba, ini poin penting)

5. **Coba kirim `menu_id` dari opsi yang bukan miliknya** (misal `option_value_ids`
   milik menu lain) → harusnya dapat error 422 "opsi tidak valid untuk menu ini"

6. **Coba pesan menu yang `is_available: false`** (menu "Es Teh" dari seeder) →
   harusnya ditolak dengan error, bukan malah berhasil dibuat

### C. Test webhook (simulasi payment sukses)

Karena webhook asli dari Midtrans butuh URL publik (server kamu tidak bisa diakses
dari internet selama masih localhost), ada 2 cara testing:

**Cara 1 — Simulasi manual via Postman (paling gampang, dari server sendiri):**

Buka `php artisan tinker` di CMD, lalu jalankan (ganti `order_id` dan `gross_amount`
sesuai order yang tadi kamu buat):
```php
$serverKey = config('midtrans.server_key');
$orderId = 'isi-uuid-order-tadi';
$statusCode = '200';
$grossAmount = '70000.00';
echo hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
```
Copy hasil hash-nya, lalu di Postman:
`POST http://localhost:8000/api/payments/midtrans/callback`
Body (JSON):
```json
{
  "order_id": "isi-uuid-order-tadi",
  "status_code": "200",
  "gross_amount": "70000.00",
  "transaction_status": "settlement",
  "fraud_status": "accept",
  "signature_key": "hasil_hash_dari_tinker_tadi"
}
```
Cek lagi `GET /api/orders/{id}` → `payment_status` harusnya sudah jadi `success`
dan `paid_at` terisi.

**Cara 2 — Pakai ngrok (untuk test alur Snap sungguhan, opsional di tahap ini):**
Install ngrok, jalankan `ngrok http 8000`, copy URL publik yang muncul (misal
`https://xxxx.ngrok-free.app`), lalu daftarkan di Midtrans Dashboard Sandbox →
**Settings → Configuration → Payment Notification URL** diisi
`https://xxxx.ngrok-free.app/api/payments/midtrans/callback`. Baru setelah itu,
kalau kamu buka halaman Snap dari `snap_token` dan benar-benar klik "bayar" pakai
kartu test Midtrans, webhook asli akan masuk ke server lokal kamu lewat ngrok.

7. **Test signature palsu (keamanan paling penting):** ulangi request webhook tapi
   ganti `signature_key` jadi string sembarangan → harusnya dapat `403 Invalid
   signature`, dan `payment_status` order **tidak berubah**. Ini bukti server tidak
   akan percaya webhook palsu.

## Checklist Sebelum Lanjut ke Milestone 4

- [ ] Order berhasil dibuat, `total_price` dihitung dari server (coba kirim harga
      palsu di body request kalau mau, tetap diabaikan karena kita tidak pernah baca
      field harga dari client)
- [ ] Opsi yang bukan milik menu tersebut ditolak
- [ ] Menu `is_available: false` tidak bisa dipesan
- [ ] Webhook dengan signature valid berhasil ubah `payment_status` jadi `success`
- [ ] Webhook dengan signature palsu ditolak (403), status order tidak berubah

Kalau semua lolos, lanjut ke **Milestone 4 — Kitchen API + Realtime** (endpoint
update status masak + broadcasting event pakai Reverb, supaya order yang baru
`success` ini benar-benar sampai real-time ke Kitchen Dashboard).
