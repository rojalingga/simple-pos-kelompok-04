<?php

namespace Tests\Feature;

use App\Jobs\ProcessProductImport;
use App\Models\Category;
use App\Models\ImportError;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImportErrorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_failed_import_rows_are_stored_and_displayed(): void
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

        (new ProcessProductImport('imports/produk.csv', $admin->id, 'produk.csv'))->handle();

        $this->assertDatabaseHas('products', [
            'name' => 'Kopi',
            'price' => 10000,
        ]);

        $this->assertDatabaseHas('import_errors', [
            'file_name' => 'produk.csv',
            'row_number' => 3,
        ]);
        $this->assertDatabaseHas('import_errors', [
            'file_name' => 'produk.csv',
            'row_number' => 4,
        ]);
        $this->assertDatabaseHas('import_errors', [
            'file_name' => 'produk.csv',
            'row_number' => 5,
        ]);

        $this->assertSame(3, ImportError::count());

        $this->actingAs($admin)
            ->get('/import')
            ->assertOk()
            ->assertSee('produk.csv')
            ->assertSee('Baris 3')
            ->assertSee('Baris 4')
            ->assertSee('Baris 5');
    }

    public function test_uploading_csv_displays_failed_rows_after_processing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::forceCreate(['name' => 'Makanan']);

        Storage::fake('local');

        $csv = implode("\n", [
            'name,category_id,price',
            'Kopi,'.$category->id.',10000',
            'Teh,tidak-ada,5000',
        ]);

        $file = UploadedFile::fake()->createWithContent('produk.csv', $csv);

        $this->actingAs($admin)
            ->post('/import', ['csv' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => 'Kopi']);
        $this->assertDatabaseHas('import_errors', [
            'file_name' => 'produk.csv',
            'row_number' => 3,
        ]);

        $this->actingAs($admin)
            ->get('/import')
            ->assertOk()
            ->assertSee('produk.csv')
            ->assertSee('Baris 3');
    }
}
