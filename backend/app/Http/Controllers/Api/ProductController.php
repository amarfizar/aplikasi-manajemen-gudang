<?php

namespace App\Http\Controllers\Api;

use App\Exports\GenericTableExport;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends CrudController
{
    protected string $modelClass = Product::class;

    protected array $searchable = ['name', 'sku', 'brand'];

    protected array $with = ['category', 'unit', 'supplier', 'location'];

    protected array $filters = ['status', 'category_id', 'supplier_id', 'warehouse_location_id'];

    protected function permissionPrefix(): string
    {
        return 'products';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', "unique:products,sku,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'estimated_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warehouse_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'image' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function nextSku(): JsonResponse
    {
        abort_unless(request()->user()?->can('products.create'), 403);

        $last = Product::withTrashed()->where('sku', 'like', 'BRG-%')->orderByDesc('id')->first();
        $number = $last ? (int) substr($last->sku, 4) + 1 : 1;

        return response()->json([
            'success' => true,
            'message' => 'SKU berikutnya.',
            'data' => ['sku' => 'BRG-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT)],
        ]);
    }

    public function importTemplate(): BinaryFileResponse
    {
        abort_unless(request()->user()?->can('products.create'), 403);

        $export = new GenericTableExport(
            collect([['BRG-000100', 'Contoh Barang', 'ELK', 'pcs', 'Merek', 10, 100000, 150000, 'SUP-001', 'A-01', 'Deskripsi']]),
            ['sku', 'name', 'category_code', 'unit_symbol', 'brand', 'minimum_stock', 'purchase_price', 'estimated_price', 'supplier_code', 'warehouse_location_code', 'description']
        );

        return Excel::download($export, 'template-import-barang.xlsx');
    }

    public function import(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('products.create'), 403);

        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/csv,application/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream', 'max:5120'],
        ]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => "Import selesai. {$import->inserted} data masuk.",
            'data' => [
                'inserted' => $import->inserted,
                'errors' => $import->errors,
            ],
        ]);
    }
}
