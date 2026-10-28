# Simple POS

Aplikasi point of sale sederhana berbasis Laravel untuk mencatat transaksi, melihat riwayat penjualan, serta mengelola produk dan kategori.

Halaman `/` menampilkan informasi untuk tamu. Pengguna yang sudah login akan diarahkan ke `/pos`.

## Menjalankan Aplikasi

```bash
php artisan migrate:fresh --seed
php artisan serve
npm install
npm run dev
```

Buka `http://127.0.0.1:8000`. Login tersedia di `/login`.

## Akun Demo

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | admin@pos.test | password |
| Kasir | kasir@pos.test | password |
| Manager | manager@pos.test | password |

## Skenario Uji Manual

### Admin

Langkah:

1. Buka `/login`, lalu masuk dengan `admin@pos.test` dan kata sandi `password`.
2. Buka `/transactions`.
3. Buka `/products`.
4. Buka `/categories`.

Hasil yang diharapkan:

- Login berhasil dan pengguna diarahkan ke `/pos`.
- Navigasi menampilkan menu Kasir, Transaksi, Produk, dan Kategori.
- Halaman `/transactions`, `/products`, dan `/categories` dapat dibuka dan menampilkan data tanpa error.

### Kasir

Langkah:

1. Keluar dari akun admin, lalu masuk dengan `kasir@pos.test` dan kata sandi `password`.
2. Buka `/pos`.
3. Ketik `/transactions` langsung di address bar.
4. Ketik `/products` langsung di address bar.

Hasil yang diharapkan:

- Login berhasil dan pengguna diarahkan ke `/pos`.
- Navigasi hanya menampilkan menu Kasir.
- Halaman `/pos` dapat digunakan untuk membuat transaksi.
- `/transactions` menampilkan halaman 403 dengan pesan "Halaman ini hanya untuk peran admin atau manager."
- `/products` menampilkan halaman 403 dengan pesan "Halaman ini hanya untuk peran admin."

### Manager

Langkah:

1. Keluar dari akun kasir, lalu masuk dengan `manager@pos.test` dan kata sandi `password`.
2. Buka `/transactions`.
3. Ketik `/products` langsung di address bar.
4. Ketik `/categories` langsung di address bar.

Hasil yang diharapkan:

- Login berhasil dan pengguna diarahkan ke `/pos`.
- Navigasi menampilkan menu Kasir dan Transaksi, tanpa menu Produk dan Kategori.
- Halaman `/transactions` dapat dibuka dan menampilkan riwayat transaksi.
- `/products` dan `/categories` menampilkan halaman 403 dengan pesan "Halaman ini hanya untuk peran admin."