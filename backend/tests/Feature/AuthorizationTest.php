<?php

namespace Tests\Feature;

class AuthorizationTest extends FeatureTestCase
{
    public function test_viewer_cannot_create_product(): void
    {
        $user = $this->createUserWithRole('viewer');

        $this->postJson('/api/products', [
            'sku' => 'X', 'name' => 'X', 'category_id' => 1, 'unit_id' => 1,
            'minimum_stock' => 0, 'status' => 'active',
        ], $this->authHeader($user))->assertStatus(403);
    }

    public function test_viewer_can_view_products(): void
    {
        $user = $this->createUserWithRole('viewer');

        $this->getJson('/api/products', $this->authHeader($user))->assertOk();
    }

    public function test_viewer_cannot_create_transaction(): void
    {
        $user = $this->createUserWithRole('viewer');

        $this->postJson('/api/transactions/in', [
            'date' => '2026-10-04',
            'warehouse_id' => 1,
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ], $this->authHeader($user))->assertStatus(403);
    }

    public function test_admin_gudang_cannot_manage_users(): void
    {
        $user = $this->createUserWithRole('admin-gudang');

        $this->getJson('/api/users', $this->authHeader($user))->assertStatus(403);
    }

    public function test_admin_gudang_can_create_stock_in(): void
    {
        $user = $this->createUserWithRole('admin-gudang');

        // admin-gudang memiliki transactions.in.create; gudang tidak ada -> 422, bukan 403
        $response = $this->postJson('/api/transactions/in', [
            'date' => '2026-10-04',
            'warehouse_id' => 999,
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ], $this->authHeader($user));

        $response->assertStatus(422);
    }
}
