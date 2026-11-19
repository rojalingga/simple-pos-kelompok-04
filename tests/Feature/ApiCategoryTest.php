<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_categories(): void
    {
        $this->getJson('/api/categories')
            ->assertUnauthorized();
    }

    public function test_token_without_ability_is_forbidden(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['products:read']
        );

        $this->getJson('/api/categories')
            ->assertForbidden();
    }

    public function test_categories_are_listed_alphabetically_with_product_count(): void
    {
        $minuman = Category::create(['name' => 'Minuman']);
        Category::create(['name' => 'Makanan']);

        Product::create([
            'category_id' => $minuman->id,
            'name' => 'Kopi',
            'price' => 15000,
            'stock' => 10,
        ]);

        Sanctum::actingAs(
            User::factory()->create(),
            ['categories:read']
        );

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Makanan')
            ->assertJsonPath('data.0.products_count', 0)
            ->assertJsonPath('data.1.name', 'Minuman')
            ->assertJsonPath('data.1.products_count', 1);
    }
}
