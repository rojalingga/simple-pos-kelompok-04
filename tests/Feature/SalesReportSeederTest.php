<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\SalesReportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalesReportSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_sales_report_seeder_fills_the_current_month(): void
    {
        $this->seed(DemoUserSeeder::class);

        $seeder = new SalesReportSeeder;
        $seeder->transactionCount = 25;
        $seeder->run();

        $this->assertSame(25, Transaction::count());
        $this->assertGreaterThan(0, Transaction::sum('total'));

        $this->assertSame(
            25,
            Transaction::query()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count(),
        );

        $this->assertGreaterThan(0, DB::table('transaction_details')->count());
    }

    public function test_report_page_shows_non_zero_sales_after_seeding(): void
    {
        $this->seed(DemoUserSeeder::class);

        $seeder = new SalesReportSeeder;
        $seeder->transactionCount = 25;
        $seeder->run();

        $expectedTotal = number_format(Transaction::sum('total'));

        $admin = User::query()->where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get('/reports')
            ->assertOk()
            ->assertSee($expectedTotal);
    }
}
