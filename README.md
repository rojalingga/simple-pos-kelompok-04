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

## Proses yang Layak Dipindah ke Queued Job

### Export laporan PDF (`GET /reports/export/pdf`)

Proses ini cocok dijadikan queued job karena saat ini seluruh pekerjaannya
dilakukan di dalam request HTTP:

1. `ReportController::exportPdf()` menjalankan beberapa query agregasi
   (`sum`, `count`, daftar 50 transaksi terbaru, dan 10 produk terlaris).
2. Hasilnya dirender menjadi HTML melalui Blade `reports.pdf`.
3. DomPDF mengubah HTML tersebut menjadi berkas PDF, lalu dikirim langsung
   sebagai response download.

Alasan proses ini lebih baik di-queue:

- **Beban CPU dan memori tinggi.** Rendering DomPDF bekerja di memori proses
  PHP. Semakin panjang rentang tanggal dan semakin banyak transaksi, semakin
  besar HTML yang harus diproses. Pekerjaan seperti ini dapat menghabiskan
  waktu lebih lama daripada batas `max_execution_time` dan berisiko
  `memory_limit` habis, sehingga request gagal di tengah jalan.
- **Tidak perlu ditunggu.** Pengguna tidak harus menahan koneksi selama PDF
  dibuat. Request cukup membuat job lalu langsung mengembalikan respons,
  sehingga halaman tetap responsif dan tidak terkena timeout web server.
- **Lebih tahan gagal.** Job punya `tries` dan `backoff`, jadi kegagalan
  sementara (misalnya database lambat) bisa dicoba ulang otomatis. Jika
  dijalankan langsung di request, pengguna harus menekan tombol export lagi
  secara manual.
- **Bisa dibatasi dan diprioritaskan.** Worker dapat diatur jumlahnya dan
  dipisahkan dari proses web, sehingga beberapa export besar tidak saling
  berebut resource dengan permintaan kasir di `/pos`.
- **Pola yang sudah dipakai.** Import produk sudah berjalan lewat
  `ProcessProductImport` dan `ProcessProductImportExcel` pada koneksi
  `database` (`QUEUE_CONNECTION=database`), jadi infrastruktur queue dan
  worker (`php artisan queue:work`) sudah tersedia.

Konsekuensi yang perlu disiapkan jika dipindah:

- **Status pekerjaan.** Perlu penanda status (misalnya `pending`, `processing`,
  `done`, `failed`) agar pengguna tahu kapan PDF siap.
- **Penyimpanan sementara.** PDF disimpan dulu di disk (misalnya
  `storage/app/reports`), bukan langsung dikirim sebagai response.
- **Unduhan aman.** Gunakan signed URL atau route ber-middleware agar berkas
  hanya bisa diunduh oleh pengguna yang berhak.
- **Pembersihan berkas.** Tambahkan scheduled command untuk menghapus PDF lama
  supaya disk tidak penuh.
- **Notifikasi.** Beri tahu pengguna saat job selesai, misalnya lewat badge di
  halaman laporan atau email, karena hasilnya tidak lagi instan.

Export CSV dan Excel tidak dipilih karena keduanya mengambil data bertahap:
CSV memakai `lazyById()` di dalam `streamed response`, sedangkan Excel memakai
`FromQuery` sehingga baris diproses per chunk tanpa menahan seluruh hasil di
memori. PDF justru harus dirender utuh lebih dulu, sehingga paling terdampak
bila data laporan membesar.