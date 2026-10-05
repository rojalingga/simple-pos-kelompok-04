<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB; use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function create()
    {
        $products = Product::where('stock', '>', 0)->paginate(12);
        return view('pos.create', ['products' => $products]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $transaction = Transaction::create([
                'user_id' => 1, // hardcode user_id ke 1 sementara
                'total' => 0,
            ]);

            $total = 0;

            $merged = []; foreach ($validated['items'] as $item) { $pid = $item['product_id'];
            $merged[$pid] = ($merged[$pid] ?? 0) + (int) $item['qty']; }
            foreach ($merged as $productId => $qty) {
                $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail(); if ($qty > $product->stock)
                { throw ValidationException::withMessages(
                    ["items" => ["Stok produk " . $product->name . " tidak mencukupi (stok: " . $product->stock . ", diminta: " . $qty . ")."]]); }
                $subtotal = $product->price * $qty;
                $total += $subtotal; $product->decrement("stock", $qty);

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ]);
            }

            $transaction->update(['total' => $total]);
        });

        return redirect()
            ->route('pos.create')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    public function index()
    {
        $transactions = Transaction::with(['details.product', 'user'])
            ->latest()
            ->paginate(15);
        return view('transactions.index', compact('transactions'));
    }

    public function show(string $id)
    {
        return "Detail transaksi #{$id}";
    }
}
