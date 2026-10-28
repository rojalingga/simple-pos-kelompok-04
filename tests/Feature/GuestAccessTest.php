<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_can_open_info_page_at_root(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Selamat datang di Simple POS');

        $this->get('/info')->assertOk();
    }

    public function test_authenticated_user_is_redirected_from_root_to_pos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('pos.create'));
    }
}