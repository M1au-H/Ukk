# Milestone 5: Customer App (Vue)

Aplikasi yang dibuka pelanggan setelah scan QR di meja: lihat menu, pilih opsi (level pedas, topping),
masukkan keranjang, isi nama, bayar lewat Midtrans, lalu pantau status pesanan secara realtime.

## Yang dibutuhkan sebelum mulai

- Node.js 18 atau lebih baru. Cek di CMD: `node -v`. Kalau belum ada, install dari https://nodejs.org (pilih versi LTS).
- Backend Laravel sudah jalan, **dua terminal terbuka**:
  - Terminal 1: `php artisan serve`
  - Terminal 2: `php artisan reverb:start`

## Cara menjalankan

1. Extract zip ini. Taruh foldernya di mana saja (tidak harus di dalam `htdocs`, ini proyek terpisah dari `restaurant-api`).
2. Buka CMD di folder `restaurant-customer-app`, lalu:
   ```
   npm install
   npm run dev
   ```
3. Buka alamat yang muncul (biasanya `http://localhost:5173`).

File `.env` sudah terisi dengan key Reverb dan Client Key Midtrans Sandbox milikmu. Kalau `REVERB_APP_KEY`
di `.env` Laravel kamu berubah, samakan nilai `VITE_REVERB_APP_KEY` di file `.env` aplikasi ini, lalu
jalankan ulang `npm run dev`.

## Cara membuka menu seperti hasil scan QR

Alamat menu formatnya `/menu/{table_token}`. Ambil `table_token` dari phpMyAdmin (database
`restaurant_ordering`, tabel `tables`), lalu buka:

```
http://localhost:5173/menu/ISI_TABLE_TOKEN
```

Contoh: `http://localhost:5173/menu/XEcVjhSSapJOozcbvEiwyj3B` (meja 1).
Membuat QR cetak dari alamat itu nanti dilakukan lewat Admin App (Milestone 6). Untuk sekarang, cukup buka
alamatnya langsung di browser.

## Skenario uji (checkpoint sebelum lanjut)

1. **Menu tampil** per kategori, nomor meja muncul di pelat hijau-kuning di kiri atas. Menu "Es Teh" tampil
   abu-abu dengan tulisan "Habis" dan tidak bisa diketuk.
2. **Pilih menu** (Nasi Goreng Spesial): tombol masukkan keranjang nonaktif sampai Level Pedas dipilih.
   Harga di tombol ikut berubah saat memilih topping dan jumlah.
3. **Stok realtime**: dengan halaman menu tetap terbuka, di Postman jalankan
   `PATCH /api/admin/menus/1/toggle-availability` (token Admin). Nasi Goreng langsung jadi "Habis" **tanpa
   refresh**. Jalankan sekali lagi untuk mengembalikan.
4. **Keranjang**: ubah jumlah, hapus, lalu tekan Pesan, isi nama, tekan Lanjut bayar.
5. **Pembayaran**: jendela Midtrans Snap terbuka. Setelah selesai atau ditutup, kamu dibawa ke halaman
   Pesanan saya.
6. **Status realtime**: dengan halaman Pesanan saya terbuka, ubah status lewat Postman
   (`PATCH /api/kitchen/orders/{id}/status`). Langkah status di kartu pesanan berubah sendiri.

## Penting: konfirmasi pembayaran saat testing di laptop

Midtrans mengirim konfirmasi lunas lewat webhook, dan server Midtrans tidak bisa menjangkau `localhost`.
Akibatnya, setelah membayar di jendela Snap, pesanan tetap tampil **"Menunggu pembayaran"** sampai webhook masuk.
Dua cara untuk melanjutkan testing:

- **Simulasi webhook** seperti di Milestone 3 (hash signature lewat `php artisan tinker`, lalu kirim lewat Postman).
  Halaman Pesanan saya memeriksa ulang setiap 5 detik selama ada pesanan yang menunggu, jadi tampilannya berubah
  sendiri tak lama setelah webhook diterima.
- **ngrok**, supaya webhook asli bisa masuk (butuh mengisi Payment Notification URL di dashboard Midtrans).

## Uji dari HP sungguhan (opsional)

1. HP dan laptop harus di WiFi yang sama. Cari IP laptop dengan `ipconfig` (misal `192.168.1.10`).
2. Jalankan backend supaya bisa diakses dari luar laptop:
   `php artisan serve --host=0.0.0.0` dan `php artisan reverb:start --host=0.0.0.0`
3. Di HP buka `http://192.168.1.10:5173/menu/ISI_TABLE_TOKEN`. Alamat API dan Reverb otomatis ikut memakai
   IP yang sama.
4. Kalau tidak bisa terbuka, Windows Firewall biasanya perlu diizinkan untuk Node.js dan PHP.

## Keputusan desain yang perlu diketahui

- **Harga tidak pernah dikirim dari aplikasi.** Yang dikirim hanya ID menu, ID opsi, jumlah, dan catatan.
  Server menghitung ulang semuanya. Total di layar hanya perkiraan.
- **Keranjang dikosongkan begitu pesanan berhasil dibuat** (sesuai aturan: setelah menekan Pesan, pesanan terkunci).
  Kalau jendela pembayaran ditutup sebelum selesai, pesanan tetap ada di halaman Pesanan saya dengan tombol
  **Lanjutkan pembayaran**. Jadi pelanggan tidak perlu mengisi ulang keranjang.
- **Channel realtime customer bersifat publik** dan memakai `session_token` acak sebagai nama channel
  (customer tidak punya akun, jadi tidak ada login untuk otorisasi channel privat).
- **Sesi tamu** dibuat saat scan QR dan disimpan di browser, terikat ke meja. Scan meja lain berarti sesi baru.

## Kalau ada masalah

- **`npm install` timeout atau gagal**: sama seperti masalah Composer sebelumnya, ini soal jaringan. Coba hotspot
  HP, atau jalankan `npm config set registry https://registry.npmmirror.com` lalu ulangi.
- **Halaman menu bilang "Tidak bisa terhubung ke server"**: pastikan `php artisan serve` jalan di port 8000. Kalau
  di browser ada tulisan CORS, kabari saya.
- **Realtime tidak bekerja (stok atau status tidak berubah sendiri)**: pastikan `reverb:start` jalan, dan
  `VITE_REVERB_APP_KEY` sama dengan `REVERB_APP_KEY` di `.env` Laravel. Error `4001` di console browser berarti
  key-nya beda.
- **Jendela pembayaran tidak muncul**: butuh internet (skrip Snap diambil dari server Midtrans), dan
  `VITE_MIDTRANS_CLIENT_KEY` harus Client Key Sandbox yang benar.

## Struktur

```
src/
  views/        LandingPage, MenuPage, CartPage, StatusPage
  components/   MenuDetailSheet (panel detail + opsi)
  stores/       session (meja & sesi tamu), cart, menu, orders
  services/     api (axios), echo (Reverb), snap (Midtrans), format
  styles.css    seluruh tampilan
```
