<nav class="bg-slate-900 text-white px-6 py-3.5 flex items-center gap-6 shadow-sm">
    <span class="font-bold tracking-wide text-emerald-400">Simple POS</span>
    
    <div class="flex items-center gap-4">
        <a href="{{ route('pos.create') }}" 
           class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('pos.create') ? 'bg-slate-800 text-white border-b-2 border-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
            Kasir
        </a>
        
        <a href="{{ route('transactions.index') }}" 
           class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('transactions.index') ? 'bg-slate-800 text-white border-b-2 border-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
            Transaksi
        </a>
    </div>
</nav>