<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\Warehouse;

class OpnameTest extends FeatureTestCase
{
    protected Product $product;

    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'E', 'code' => 'E1', 'status' => 'active']);
        $unit = Unit::create(['name' => 'pcs', 'symbol' => 'pcs', 'status' => 'active']);
        $this->warehouse = Warehouse::create(['code' => 'WH-1', 'name' => 'Gudang', 'status' => 'active']);
        $this->product = Product::create([
            'sku' => 'BRG-1', 'name' => 'Barang', 'category_id' => $category->id,
            'unit_id' => $unit->id, 'minimum_stock' => 0, 'status' => 'active',
        ]);
        ProductStock::create(['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 10]);
    }

    public function test_full_opname_flow_adjusts_stock(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $create = $this->postJson('/api/stock-opnames', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'pic' => 'Joko',
        ], $this->authHeader($user))->assertCreated();

        $opnameId = $create->json('data.id');
        $itemId = $create->json('data.items.0.id');

        $this->putJson("/api/stock-opnames/{$opnameId}/items", [
            'items' => [['id' => $itemId, 'physical_stock' => 7]],
        ], $this->authHeader($user))->assertOk();

        $this->postJson("/api/stock-opnames/{$opnameId}/review", [], $this->authHeader($user))->assertOk();

        $this->postJson("/api/stock-opnames/{$opnameId}/approve", [], $this->authHeader($user))
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertEquals(7, (float) ProductStock::where('product_id', $this->product->id)->value('quantity'));
        $this->assertDatabaseHas('stock_transactions', ['type' => 'adjustment']);
    }

    public function test_approve_requires_review_status(): void
    {
        $user = $this->createUserWithRole('super-admin');

        $create = $this->postJson('/api/stock-opnames', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
        ], $this->authHeader($user))->assertCreated();

        $this->postJson('/api/stock-opnames/'.$create->json('data.id').'/approve', [], $this->authHeader($user))
            ->assertStatus(422);
    }
}
