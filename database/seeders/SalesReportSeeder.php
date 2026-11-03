<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportSeeder extends Seeder
{
    /**
     * Jumlah transaksi demo yang dibuat.
     */
    public int $transactionCount = 2500;

    public function run(): void
    {
        $cashier = User::query()
            ->whereIn('role', ['kasir', 'admin'])
            ->inRandomOrder()
            ->first();

        if ($cashier === null) {
            $this->call(DemoUserSeeder::class);

            $cashier = User::query()
                ->whereIn('role', ['kasir', 'admin'])
                ->firstOrFail();
        }

        $productPrices = Product::query()
            ->where('is_active', true)
            ->pluck('price', 'id');

        if ($productPrices->isEmpty()) {
            $productPrices = $this->createFallbackProducts();
        }

        $productIds = $productPrices->keys()->all();

        // Sebar transaksi pada bulan berjalan agar laporan default
        // (awal bulan sampai hari ini) langsung menampilkan data.
        $start = now()->startOfMonth();
        $end = now();
        $span = max(1, (int) $start->diffInSeconds($end));

        for ($i = 0; $i < $this->transactionCount; $i++) {
            $createdAt = $start->copy()->addSeconds(random_int(0, $span));

            DB::transaction(function () use ($cashier, $productIds, $productPrices, $createdAt) {
                $itemCount = random_int(1, 4);
                $total = 0;
                $details = [];

                for ($item = 0; $item < $itemCount; $item++) {
                    $productId = $productIds[array_rand($productIds)];
                    $qty = random_int(1, 3);
                    $subtotal = $productPrices[$productId] * $qty;
                    $total += $subtotal;

                    $details[] = [
                        'product_id' => $productId,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                $transactionId = DB::table('transactions')->insertGetId([
                    'user_id' => $cashier->id,
                    'total' => $total,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($details as $index => $detail) {
                    $details[$index]['transaction_id'] = $transactionId;
                }

                DB::table('transaction_details')->insert($details);
            });
        }
    }

    /**
     * Buat kategori dan produk minimal jika database masih kosong.
     *
     * @return Collection<int, int>
     */
    private function createFallbackProducts()
    {
        $category = Category::query()->where('name', 'Makanan')->first()
            ?? Category::forceCreate(['name' => 'Makanan']);

        $samples = [
            ['name' => 'Nasi Goreng', 'price' => 15000],
            ['name' => 'Mie Ayam', 'price' => 12000],
            ['name' => 'Es Teh', 'price' => 5000],
            ['name' => 'Kopi', 'price' => 8000],
            ['name' => 'Roti Bakar', 'price' => 10000],
        ];

        foreach ($samples as $sample) {
            Product::forceCreate([
                'category_id' => $category->id,
                'name' => $sample['name'],
                'price' => $sample['price'],
                'stock' => 100,
                'is_active' => true,
            ]);
        }

        return Product::query()->pluck('price', 'id');
    }
}
