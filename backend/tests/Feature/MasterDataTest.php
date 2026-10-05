<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Unit;

class MasterDataTest extends FeatureTestCase
{
    public function test_create_category(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $response = $this->postJson('/api/categories', [
            'name' => 'Elektronik',
            'code' => 'ELK',
            'status' => 'active',
        ], $this->authHeader($user));

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('categories', ['code' => 'ELK']);
    }

    public function test_duplicate_category_code_rejected(): void
    {
        $user = $this->createUserWithRole('super-admin');
        Category::create(['name' => 'A', 'code' => 'ELK', 'status' => 'active']);

        $response = $this->postJson('/api/categories', [
            'name' => 'B',
            'code' => 'ELK',
            'status' => 'active',
        ], $this->authHeader($user));

        $response->assertStatus(422);
    }

    public function test_product_requires_unique_sku(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $category = Category::create(['name' => 'E', 'code' => 'E1', 'status' => 'active']);
        $unit = Unit::create(['name' => 'pcs', 'symbol' => 'pcs', 'status' => 'active']);

        $payload = [
            'sku' => 'BRG-1', 'name' => 'Barang A', 'category_id' => $category->id,
            'unit_id' => $unit->id, 'minimum_stock' => 5, 'status' => 'active',
        ];

        $this->postJson('/api/products', $payload, $this->authHeader($user))->assertCreated();

        $this->postJson('/api/products', $payload, $this->authHeader($user))->assertStatus(422);
    }

    public function test_category_soft_delete(): void
    {
        $user = $this->createUserWithRole('super-admin');
        $category = Category::create(['name' => 'A', 'code' => 'A1', 'status' => 'active']);

        $this->deleteJson("/api/categories/{$category->id}", [], $this->authHeader($user))->assertOk();

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
