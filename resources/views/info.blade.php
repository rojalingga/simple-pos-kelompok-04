<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi - Simple POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-slate-300/50 blur-3xl"></div>

        <div class="relative w-full max-w-3xl">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 4.5 4.5h15L21 9.75M3 9.75h18M3 9.75v9.75h18V9.75M9 19.5v-6h6v6" />
                    </svg>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Selamat datang di Simple POS</h1>
                <p class="mx-auto mt-2 max-w-xl text-sm text-slate-500">
                    Aplikasi kasir sederhana untuk mencatat penjualan, memantau stok, dan mengelola produk kafe dalam satu tempat.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-800">Transaksi cepat</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Pilih produk, atur jumlah, lalu simpan transaksi langsung dari halaman kasir.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-800">Riwayat penjualan</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Admin dan manager dapat meninjau transaksi beserta rincian item yang terjual.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-800">Manajemen produk</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Admin dapat menambah produk, mengatur harga, dan memperbarui stok dengan mudah.</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-center sm:text-left">
                        <p class="font-medium text-slate-800">Halaman ini khusus untuk tamu.</p>
                        <p class="mt-1 text-sm text-slate-500">Masuk untuk mulai mencatat transaksi di Simple POS.</p>
                    </div>
                    <a href="{{ route('login') }}"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                        Masuk ke Aplikasi
                    </a>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} Simple POS</p>
        </div>
    </div>
</body>
</html>