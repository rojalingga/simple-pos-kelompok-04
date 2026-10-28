<nav class="border-b border-slate-800 bg-slate-900 text-white">
    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3">
        <a href="{{ route('pos.create') }}" class="flex items-center gap-2 font-semibold tracking-tight">
            <span class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-600 text-xs font-bold">SP</span>
            <span>Simple POS</span>
        </a>

        <div class="flex flex-wrap items-center gap-1 text-sm">
            <a href="{{ route('pos.create') }}"
                @class([
                    'rounded-md px-2.5 py-1.5 transition hover:bg-slate-800 hover:text-white',
                    'bg-slate-800 text-white' => request()->routeIs('pos.*'),
                    'text-slate-300' => ! request()->routeIs('pos.*'),
                ])>
                Kasir
            </a>

            @if (auth()->user()?->hasRole('admin', 'manager'))
                <a href="{{ route('transactions.index') }}"
                    @class([
                        'rounded-md px-2.5 py-1.5 transition hover:bg-slate-800 hover:text-white',
                        'bg-slate-800 text-white' => request()->routeIs('transactions.*'),
                        'text-slate-300' => ! request()->routeIs('transactions.*'),
                    ])>
                    Transaksi
                </a>
            @endif

            @if (auth()->user()?->isAdmin())
                <a href="{{ route('products.index') }}"
                    @class([
                        'rounded-md px-2.5 py-1.5 transition hover:bg-slate-800 hover:text-white',
                        'bg-slate-800 text-white' => request()->routeIs('products.*'),
                        'text-slate-300' => ! request()->routeIs('products.*'),
                    ])>
                    Produk
                </a>

                <a href="{{ route('categories.index') }}"
                    @class([
                        'rounded-md px-2.5 py-1.5 transition hover:bg-slate-800 hover:text-white',
                        'bg-slate-800 text-white' => request()->routeIs('categories.*'),
                        'text-slate-300' => ! request()->routeIs('categories.*'),
                    ])>
                    Kategori
                </a>

                <a href="{{ route('reports.index') }}"
                    @class([
                        'rounded-md px-2.5 py-1.5 transition hover:bg-slate-800 hover:text-white',
                        'bg-slate-800 text-white' => request()->routeIs('reports.*'),
                        'text-slate-300' => ! request()->routeIs('reports.*'),
                    ])>
                    Laporan
                </a>
                <a href="{{ route('import.create') }}" class="hover:underline">Import</a>
            @endif
        </div>

        <div class="ml-auto flex items-center gap-3">
            <span class="hidden items-center gap-2 text-sm sm:flex">
                <span class="text-slate-300">{{ auth()->user()?->name }}</span>
                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-400">
                    {{ auth()->user()?->role }}
                </span>
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="rounded-md border border-slate-700 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>