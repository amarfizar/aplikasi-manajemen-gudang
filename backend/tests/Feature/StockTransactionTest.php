<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Unit;
use App\Models\Warehouse;

class StockTransactionTest extends FeatureTestCase
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
    }

    public function test_stock_in_increases_stock_and_creates_history(): void
    {
        $user = $this->createUserWithRole('admin-gudang');

        $response = $this->postJson('/api/transactions/in', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 20, 'purchase_price' => 10000],
            ],
        ], $this->authHeader($user));

        $response->assertCreated();
        $this->assertEquals(20, (float) ProductStock::where('product_id', $this->product->id)->value('quantity'));
        $this->assertDatabaseHas('stock_transactions', ['type' => 'in', 'number' => 'IN-'.now()->format('Ymd').'-0001']);
    }

    public function test_stock_out_decreases_stock(): void
    {
        $user = $this->createUserWithRole('admin-gudang');
        ProductStock::create(['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 15]);

        $response = $this->postJson('/api/transactions/out', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'destination' => 'Marketing',
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5],
            ],
        ], $this->authHeader($user));

        $response->assertCreated();
        $this->assertEquals(10, (float) ProductStock::where('product_id', $this->product->id)->value('quantity'));
    }

    public function test_stock_out_fails_when_insufficient(): void
    {
        $user = $this->createUserWithRole('admin-gudang');
        ProductStock::create(['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 3]);

        $response = $this->postJson('/api/transactions/out', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 10],
            ],
        ], $this->authHeader($user));

        $response->assertStatus(422)->assertJsonPath('message', 'Stok tidak mencukupi untuk barang yang dipilih.');
        $this->assertEquals(3, (float) ProductStock::where('product_id', $this->product->id)->value('quantity'));
    }

    public function test_adjustment_records_before_and_after(): void
    {
        $user = $this->createUserWithRole('admin-gudang');
        ProductStock::create(['product_id' => $this->product->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 10]);

        $response = $this->postJson('/api/stock-adjustments', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'change' => -3,
            'reason' => 'Kerusakan',
        ], $this->authHeader($user));

        $response->assertCreated();
        $this->assertEquals(7, (float) ProductStock::where('product_id', $this->product->id)->value('quantity'));
        $this->assertDatabaseHas('stock_transaction_items', ['stock_before' => 10, 'stock_after' => 7]);
    }

    public function test_stock_history_endpoint(): void
    {
        $user = $this->createUserWithRole('admin-gudang');

        $this->postJson('/api/transactions/in', [
            'date' => '2026-10-04',
            'warehouse_id' => $this->warehouse->id,
            'items' => [['product_id' => $this->product->id, 'quantity' => 10]],
        ], $this->authHeader($user));

        $response = $this->getJson('/api/stock-histories', $this->authHeader($user));

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data'));
    }
}
