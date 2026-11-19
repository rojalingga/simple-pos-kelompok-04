<?php

namespace Tests\Feature;

use App\Models\IdempotencyKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdempotencyKeyPruningTest extends TestCase
{
    use RefreshDatabase;

    public function test_keys_older_than_24_hours_are_pruned(): void
    {
        $user = User::factory()->create();

        $old = IdempotencyKey::create([
            'user_id' => $user->id,
            'key' => 'old-key',
            'request_hash' => hash('sha256', 'old-request'),
        ]);
        $old->forceFill(['created_at' => now()->subHours(25)])->saveQuietly();

        $recent = IdempotencyKey::create([
            'user_id' => $user->id,
            'key' => 'recent-key',
            'request_hash' => hash('sha256', 'recent-request'),
        ]);

        $this->artisan('model:prune', ['--model' => [IdempotencyKey::class]])
            ->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }
}
