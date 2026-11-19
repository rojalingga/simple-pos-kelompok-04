<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiClientDemoTest extends TestCase
{
    public function test_demo_stops_after_failed_login_without_using_empty_token(): void
    {
        Http::fake([
            'http://127.0.0.1:8000/api/v1/login' => Http::response([
                'message' => 'Kredensial tidak valid.',
            ], 401),
        ]);

        $this->artisan('api:demo')
            ->expectsOutput('Login gagal: Kredensial tidak valid.')
            ->expectsOutput('Command berhenti sebelum memakai token kosong.')
            ->assertFailed();

        Http::assertSentCount(1);
        Http::assertNotSent(
            fn ($request) => str_contains($request->url(), '/products')
                || str_contains($request->url(), '/transactions')
        );
    }
}
