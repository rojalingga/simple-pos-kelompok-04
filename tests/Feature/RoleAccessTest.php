<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_manager_can_open_transactions_but_not_products(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->get('/transactions')->assertOk();
        $this->actingAs($manager)->get('/products')->assertForbidden();
    }

    public function test_admin_can_open_transactions_and_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/transactions')->assertOk();
        $this->actingAs($admin)->get('/products')->assertOk();
    }

    public function test_kasir_cannot_open_transactions_or_products(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->get('/transactions')->assertForbidden();
        $this->actingAs($kasir)->get('/products')->assertForbidden();
    }
}