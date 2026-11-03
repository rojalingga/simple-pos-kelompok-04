<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function test_pdf_report_includes_top_selling_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::forceCreate(['name' => 'Makanan']);

        $food = Product::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 10000,
            'stock' => 10,
        ]);
        $drink = Product::create([
            'category_id' => $category->id,
            'name' => 'Es Teh',
            'price' => 5000,
            'stock' => 10,
        ]);

        $foodTransaction = Transaction::create(['user_id' => $admin->id, 'total' => 30000]);
        TransactionDetail::create([
            'transaction_id' => $foodTransaction->id,
            'product_id' => $food->id,
            'qty' => 3,
            'subtotal' => 30000,
        ]);

        $drinkTransaction = Transaction::create(['user_id' => $admin->id, 'total' => 5000]);
        TransactionDetail::create([
            'transaction_id' => $drinkTransaction->id,
            'product_id' => $drink->id,
            'qty' => 1,
            'subtotal' => 5000,
        ]);

        $capturedData = null;

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use (&$capturedData) {
                $capturedData = $data;

                return $view === 'reports.pdf';
            })
            ->andReturnSelf();
        Pdf::shouldReceive('download')
            ->once()
            ->andReturn(response('pdf-content'));

        $this->actingAs($admin)
            ->get('/reports/export/pdf')
            ->assertOk();

        $topProducts = collect($capturedData['topProducts']);

        $this->assertCount(2, $topProducts);
        $this->assertSame('Nasi Goreng', $topProducts->first()->name);
        $this->assertSame(3, (int) $topProducts->first()->total_qty);
        $this->assertSame(30000, (int) $topProducts->first()->total_sales);
    }

    public function test_pdf_report_can_be_generated_with_top_products_table(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/reports/export/pdf')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
