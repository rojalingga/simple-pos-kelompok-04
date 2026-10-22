<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Simple POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-slate-300/50 blur-3xl"></div>

        <div class="relative w-full max-w-sm">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75 4.5 4.5h15L21 9.75M3 9.75h18M3 9.75v9.75h18V9.75M9 19.5v-6h6v6" />
                    </svg>
                </div>
                <h1 class="text-xl font-semibold tracking-tight text-slate-900">Simple POS</h1>
                <p class="mt-1 text-sm text-slate-500">Masuk untuk mulai transaksi</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@pos.test"
                            @class([
                                'w-full rounded-lg border bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-2',
                                'border-slate-300 focus:border-blue-500 focus:ring-blue-500/20' => ! $errors->has('email'),
                                'border-red-400 focus:border-red-500 focus:ring-red-500/20' => $errors->has('email'),
                            ])>
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                        <input id="password" type="password" name="password" required
                            placeholder="Masukkan kata sandi"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-600">
                    <p class="font-medium text-slate-700">Akun demo</p>
                    <div class="mt-2 space-y-1">
                        <p><span class="text-slate-500">Admin:</span> admin@pos.test</p>
                        <p><span class="text-slate-500">Kasir:</span> kasir@pos.test</p>
                        <p><span class="text-slate-500">Manager:</span> manager@pos.test</p>
                    </div>
                    <p class="mt-2 text-slate-500">
                        Kata sandi:
                        <code class="rounded bg-white px-1.5 py-0.5 font-mono text-slate-700 ring-1 ring-slate-200">password</code>
                    </p>
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-slate-400">
                <a href="{{ route('info') }}" class="transition hover:text-slate-600">Tentang Simple POS</a>
                <span class="mx-1.5">&middot;</span>
                <span>&copy; {{ date('Y') }} Simple POS</span>
            </p>
        </div>
    </div>
</body>
</html>