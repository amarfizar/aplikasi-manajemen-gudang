<?php

namespace App\Services;

use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function getCurrentStock(int $productId, int $warehouseId): float
    {
        return (float) (ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0);
    }

    public function increaseStock(int $productId, int $warehouseId, float $quantity): ProductStock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = $this->lockedStock($productId, $warehouseId);
            $stock->increment('quantity', $quantity);

            return $stock->fresh();
        });
    }

    public function decreaseStock(int $productId, int $warehouseId, float $quantity): ProductStock
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = ProductStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (! $stock || (float) $stock->quantity < $quantity) {
                throw new RuntimeException('Stok tidak mencukupi untuk barang yang dipilih.');
            }

            $stock->decrement('quantity', $quantity);

            return $stock->fresh();
        });
    }

    public function adjustStock(int $productId, int $warehouseId, float $change): array
    {
        return DB::transaction(function () use ($productId, $warehouseId, $change) {
            $stock = $this->lockedStock($productId, $warehouseId);

            $before = (float) $stock->quantity;
            $after = $before + $change;

            if ($after < 0) {
                throw new RuntimeException('Stok tidak mencukupi untuk penyesuaian ini.');
            }

            $stock->update(['quantity' => $after]);

            return ['before' => $before, 'after' => $after, 'stock' => $stock->fresh()];
        });
    }

    protected function lockedStock(int $productId, int $warehouseId): ProductStock
    {
        ProductStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );

        return ProductStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();
    }
}
