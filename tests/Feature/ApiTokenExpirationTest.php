<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_expired_token_is_unauthorized(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken(
            'mobile-kasir',
            ['categories:read'],
            now()->addDay(),
        )->plainTextToken;

        Sanctum::actingAs($user, ['categories:read']);

        $this->getJson('/api/v1/categories')->assertOk();
        $this->app['auth']->forgetGuards();

        Carbon::setTestNow(now()->addDays(2));

        $this->withToken($token)
            ->getJson('/api/v1/categories')
            ->assertUnauthorized();
    }
}
