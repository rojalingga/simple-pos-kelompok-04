<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_report_can_be_filtered_by_product_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $food = Category::forceCreate(['name' => 'Makanan']);
        $drink = Category::forceCreate(['name' => 'Minuman']);

        $foodProduct = Product::create([
            'category_id' => $food->id,
            'name' => 'Nasi Goreng',
            'price' => 10000,
            'stock' => 10,
        ]);
        $drinkProduct = Product::create([
            'category_id' => $drink->id,
            'name' => 'Es Teh',
            'price' => 5000,
            'stock' => 10,
        ]);

        $foodTransaction = Transaction::create(['user_id' => $admin->id, 'total' => 10000]);
        TransactionDetail::create([
            'transaction_id' => $foodTransaction->id,
            'product_id' => $foodProduct->id,
            'qty' => 1,
            'subtotal' => 10000,
        ]);

        $drinkTransaction = Transaction::create(['user_id' => $admin->id, 'total' => 5000]);
        TransactionDetail::create([
            'transaction_id' => $drinkTransaction->id,
            'product_id' => $drinkProduct->id,
            'qty' => 1,
            'subtotal' => 5000,
        ]);

        $this->actingAs($admin)
            ->get('/reports?category_id='.$food->id)
            ->assertOk()
            ->assertViewHas('totalPenjualan', 10000)
            ->assertViewHas('jumlahTransaksi', 1)
            ->assertSee('Makanan');
    }
}
