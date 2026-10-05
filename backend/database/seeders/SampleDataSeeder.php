<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Services\InventoryService;
use App\Services\TransactionNumberGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'code' => 'ELK', 'description' => 'Perangkat elektronik'],
            ['name' => 'Aksesoris Komputer', 'code' => 'AKS', 'description' => 'Aksesoris PC dan laptop'],
            ['name' => 'ATK', 'code' => 'ATK', 'description' => 'Alat tulis kantor'],
        ];
        foreach ($categories as $c) {
            Category::firstOrCreate(['code' => $c['code']], $c + ['status' => 'active']);
        }

        $units = [
            ['name' => 'Pieces', 'symbol' => 'pcs'],
            ['name' => 'Unit', 'symbol' => 'unit'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u + ['status' => 'active']);
        }

        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'PT Sumber Elektronik', 'pic' => 'Budi', 'phone' => '08123456789', 'email' => 'sales@sumberel.co.id', 'address' => 'Jakarta'],
            ['code' => 'SUP-002', 'name' => 'CV Mitra Komputer', 'pic' => 'Ani', 'phone' => '08129876543', 'email' => 'info@mitrakomputer.id', 'address' => 'Bandung'],
        ];
        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['code' => $s['code']], $s + ['status' => 'active']);
        }

        $warehouses = [
            ['code' => 'WH-001', 'name' => 'Gudang Utama', 'pic' => 'Joko', 'phone' => '08111111111', 'address' => 'Jl. Raya Industri No. 1'],
            ['code' => 'WH-002', 'name' => 'Gudang Cabang', 'pic' => 'Sari', 'phone' => '08222222222', 'address' => 'Jl. Gudang No. 8'],
        ];
        foreach ($warehouses as $w) {
            Warehouse::firstOrCreate(['code' => $w['code']], $w + ['status' => 'active']);
        }

        $wh1 = Warehouse::where('code', 'WH-001')->first();
        $wh2 = Warehouse::where('code', 'WH-002')->first();

        $locations = [
            ['warehouse_id' => $wh1->id, 'code' => 'A-01', 'name' => 'Rak A-01'],
            ['warehouse_id' => $wh1->id, 'code' => 'A-02', 'name' => 'Rak A-02'],
            ['warehouse_id' => $wh2->id, 'code' => 'B-01', 'name' => 'Rak B-01'],
        ];
        foreach ($locations as $l) {
            WarehouseLocation::firstOrCreate(['warehouse_id' => $l['warehouse_id'], 'code' => $l['code']], $l + ['status' => 'active']);
        }

        $pcs = Unit::where('symbol', 'pcs')->first();
        $box = Unit::where('symbol', 'box')->first();
        $elk = Category::where('code', 'ELK')->first();
        $aks = Category::where('code', 'AKS')->first();
        $atk = Category::where('code', 'ATK')->first();
        $sup1 = Supplier::where('code', 'SUP-001')->first();
        $sup2 = Supplier::where('code', 'SUP-002')->first();
        $locA1 = WarehouseLocation::where('code', 'A-01')->first();

        $products = [
            ['sku' => 'BRG-000001', 'name' => 'Keyboard Logitech K120', 'category_id' => $aks->id, 'unit_id' => $pcs->id, 'brand' => 'Logitech', 'minimum_stock' => 10, 'purchase_price' => 120000, 'estimated_price' => 185000, 'supplier_id' => $sup2->id, 'warehouse_location_id' => $locA1->id],
            ['sku' => 'BRG-000002', 'name' => 'Mouse Wireless Logitech M331', 'category_id' => $aks->id, 'unit_id' => $pcs->id, 'brand' => 'Logitech', 'minimum_stock' => 10, 'purchase_price' => 140000, 'estimated_price' => 210000, 'supplier_id' => $sup2->id],
            ['sku' => 'BRG-000003', 'name' => 'Monitor 24 inch Samsung', 'category_id' => $elk->id, 'unit_id' => $pcs->id, 'brand' => 'Samsung', 'minimum_stock' => 5, 'purchase_price' => 1800000, 'estimated_price' => 2400000, 'supplier_id' => $sup1->id],
            ['sku' => 'BRG-000004', 'name' => 'Kertas A4 80gsm', 'category_id' => $atk->id, 'unit_id' => $box->id, 'brand' => 'PaperOne', 'minimum_stock' => 20, 'purchase_price' => 55000, 'estimated_price' => 68000, 'supplier_id' => $sup1->id],
            ['sku' => 'BRG-000005', 'name' => 'Laptop ASUS VivoBook 14', 'category_id' => $elk->id, 'unit_id' => $pcs->id, 'brand' => 'ASUS', 'minimum_stock' => 3, 'purchase_price' => 7500000, 'estimated_price' => 9200000, 'supplier_id' => $sup1->id],
        ];
        foreach ($products as $p) {
            Product::firstOrCreate(['sku' => $p['sku']], $p + ['status' => 'active']);
        }

        if (StockTransaction::where('type', 'in')->count() === 0) {
            $inventory = app(InventoryService::class);
            $numbers = app(TransactionNumberGenerator::class);
            $admin = User::where('email', 'admin@inventory.test')->first();

            DB::transaction(function () use ($inventory, $numbers, $admin, $wh1, $sup2, $pcs, $box) {
                $tx = StockTransaction::create([
                    'number' => $numbers->next('in'),
                    'type' => 'in',
                    'date' => now()->toDateString(),
                    'supplier_id' => $sup2->id,
                    'warehouse_id' => $wh1->id,
                    'reference' => 'PO-2026-001',
                    'note' => 'Stok awal',
                    'created_by' => $admin->id,
                ]);

                $items = [
                    ['sku' => 'BRG-000001', 'qty' => 50, 'price' => 120000, 'unit' => $pcs->id],
                    ['sku' => 'BRG-000002', 'qty' => 40, 'price' => 140000, 'unit' => $pcs->id],
                    ['sku' => 'BRG-000003', 'qty' => 15, 'price' => 1800000, 'unit' => $pcs->id],
                    ['sku' => 'BRG-000004', 'qty' => 100, 'price' => 55000, 'unit' => $box->id],
                    ['sku' => 'BRG-000005', 'qty' => 10, 'price' => 7500000, 'unit' => $pcs->id],
                ];

                foreach ($items as $item) {
                    $product = Product::where('sku', $item['sku'])->first();
                    $stock = $inventory->increaseStock($product->id, $wh1->id, $item['qty']);
                    $tx->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['qty'],
                        'unit_id' => $item['unit'],
                        'purchase_price' => $item['price'],
                        'subtotal' => $item['qty'] * $item['price'],
                        'stock_after' => $stock->quantity,
                    ]);
                }
            });
        }
    }
}