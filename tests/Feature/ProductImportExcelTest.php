<?php

namespace Tests\Feature;

use App\Jobs\ProcessProductImportExcel;
use App\Models\Category;
use App\Models\ImportError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImportExcelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_laravel_excel_import_stores_failed_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::forceCreate(['name' => 'Makanan']);

        Storage::fake('local');

        $csv = implode("\n", [
            'name,category_id,price',
            'Kopi,'.$category->id.',10000',
            'Teh,999999,5000',
            'Roti,'.$category->id,
            'Gula,'.$category->id.',-5',
        ]);

        Storage::disk('local')->put('imports/produk.csv', $csv);

        (new ProcessProductImportExcel('imports/produk.csv', $admin->id, 'produk.csv'))->handle();

        $this->assertDatabaseHas('products', [
            'name' => 'Kopi',
            'price' => 10000,
        ]);

        $this->assertSame(3, ImportError::count());

        foreach ([3, 4, 5] as $rowNumber) {
            $this->assertDatabaseHas('import_errors', [
                'file_name' => 'produk.csv',
                'row_number' => $rowNumber,
            ]);
        }

        Storage::assertMissing('imports/produk.csv');
    }

    public function test_import_page_can_use_laravel_excel_mode(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::forceCreate(['name' => 'Makanan']);

        Storage::fake('local');

        $csv = implode("\n", [
            'name,category_id,price',
            'Kopi,'.$category->id.',10000',
            'Teh,999999,5000',
        ]);

        $file = UploadedFile::fake()->createWithContent('produk-excel.csv', $csv);

        $this->actingAs($admin)
            ->post('/import', ['csv' => $file, 'mode' => 'excel'])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Kopi']);
        $this->assertDatabaseHas('import_errors', [
            'file_name' => 'produk-excel.csv',
            'row_number' => 3,
        ]);
    }
}
