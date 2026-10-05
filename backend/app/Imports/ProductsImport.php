<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\WarehouseLocation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public int $inserted = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $sku = trim((string) ($row['sku'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $categoryCode = trim((string) ($row['category_code'] ?? ''));
            $unitSymbol = trim((string) ($row['unit_symbol'] ?? ''));

            if ($sku === '' || $name === '' || $categoryCode === '' || $unitSymbol === '') {
                $this->errors[] = "Baris {$rowNumber}: sku, name, category_code, unit_symbol wajib diisi.";

                continue;
            }

            if (Product::where('sku', $sku)->exists()) {
                $this->errors[] = "Baris {$rowNumber}: SKU {$sku} sudah ada.";

                continue;
            }

            $category = Category::where('code', $categoryCode)->first();
            $unit = Unit::where('symbol', $unitSymbol)->first();

            if (! $category || ! $unit) {
                $this->errors[] = "Baris {$rowNumber}: kategori/satuan tidak ditemukan.";

                continue;
            }

            $supplier = ! empty($row['supplier_code']) ? Supplier::where('code', trim((string) $row['supplier_code']))->first() : null;
            $location = ! empty($row['warehouse_location_code'])
                ? WarehouseLocation::where('code', trim((string) $row['warehouse_location_code']))->first()
                : null;

            Product::create([
                'sku' => $sku,
                'name' => $name,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'brand' => $row['brand'] ?? null,
                'minimum_stock' => is_numeric($row['minimum_stock'] ?? null) ? (float) $row['minimum_stock'] : 0,
                'purchase_price' => is_numeric($row['purchase_price'] ?? null) ? (float) $row['purchase_price'] : null,
                'estimated_price' => is_numeric($row['estimated_price'] ?? null) ? (float) $row['estimated_price'] : null,
                'supplier_id' => $supplier?->id,
                'warehouse_location_id' => $location?->id,
                'description' => $row['description'] ?? null,
                'status' => 'active',
            ]);

            $this->inserted++;
        }
    }
}
