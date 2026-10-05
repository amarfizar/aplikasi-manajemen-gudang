<?php

namespace App\Http\Controllers\Api;

use App\Exports\GenericTableExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransaction;
use App\Models\StockTransactionItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class ReportController extends Controller
{
    public function stock(Request $request): mixed
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        $query = ProductStock::with(['product.category', 'product.unit', 'warehouse'])
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->whereNull('products.deleted_at')
            ->select('product_stocks.*');

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('product_stocks.warehouse_id', $warehouseId);
        }
        if ($categoryId = $request->get('category_id')) {
            $query->where('products.category_id', $categoryId);
        }

        $rows = $query->get()->map(function ($s) {
            $qty = (float) $s->quantity;
            $min = (float) ($s->product->minimum_stock ?? 0);

            return [
                'sku' => $s->product->sku,
                'barang' => $s->product->name,
                'kategori' => $s->product->category?->name,
                'gudang' => $s->warehouse->name,
                'stok' => $qty,
                'minimum_stok' => $min,
                'status' => $qty <= 0 ? 'Habis' : ($qty <= $min ? 'Menipis' : 'Aman'),
            ];
        });

        if ($export = $request->get('export')) {
            return $this->exportRows(
                $rows->map(fn ($r) => array_values($r)),
                ['SKU', 'Barang', 'Kategori', 'Gudang', 'Stok', 'Minimum Stok', 'Status'],
                'laporan-stok',
                $export,
                'Laporan Stok'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan stok.',
            'data' => $rows,
        ]);
    }

    public function stockIn(Request $request): mixed
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        $query = StockTransactionItem::with(['transaction.supplier', 'transaction.warehouse', 'product', 'unit'])
            ->whereHas('transaction', fn ($q) => $q->where('type', 'in'));

        $this->applyTransactionFilters($query, $request, 'in');

        $rows = $query->get()->map(fn ($it) => [
            'tanggal' => $it->transaction->date->format('Y-m-d'),
            'nomor' => $it->transaction->number,
            'supplier' => $it->transaction->supplier?->name,
            'gudang' => $it->transaction->warehouse->name,
            'barang' => $it->product->name,
            'qty' => (float) $it->quantity,
            'harga' => $it->purchase_price !== null ? (float) $it->purchase_price : null,
            'subtotal' => $it->subtotal !== null ? (float) $it->subtotal : null,
        ]);

        if ($export = $request->get('export')) {
            return $this->exportRows(
                $rows->map(fn ($r) => array_values($r)),
                ['Tanggal', 'Nomor', 'Supplier', 'Gudang', 'Barang', 'Qty', 'Harga', 'Subtotal'],
                'laporan-barang-masuk',
                $export,
                'Laporan Barang Masuk'
            );
        }

        return response()->json(['success' => true, 'message' => 'Laporan barang masuk.', 'data' => $rows]);
    }

    public function stockOut(Request $request): mixed
    {
        abort_unless($request->user()?->can('reports.view'), 403);

        $query = StockTransactionItem::with(['transaction.warehouse', 'product', 'unit'])
            ->whereHas('transaction', fn ($q) => $q->where('type', 'out'));

        $this->applyTransactionFilters($query, $request, 'out');

        $rows = $query->get()->map(fn ($it) => [
            'tanggal' => $it->transaction->date->format('Y-m-d'),
            'nomor' => $it->transaction->number,
            'gudang' => $it->transaction->warehouse->name,
            'tujuan' => $it->transaction->destination,
            'departemen' => $it->transaction->department,
            'barang' => $it->product->name,
            'qty' => (float) $it->quantity,
        ]);

        if ($export = $request->get('export')) {
            return $this->exportRows(
                $rows->map(fn ($r) => array_values($r)),
                ['Tanggal', 'Nomor', 'Gudang', 'Tujuan', 'Departemen', 'Barang', 'Qty'],
                'laporan-barang-keluar',
                $export,
                'Laporan Barang Keluar'
            );
        }

        return response()->json(['success' => true, 'message' => 'Laporan barang keluar.', 'data' => $rows]);
    }

    protected function applyTransactionFilters($query, Request $request, string $type): void
    {
        $query->whereHas('transaction', function ($q) use ($request) {
            if ($from = $request->get('from')) {
                $q->whereDate('date', '>=', $from);
            }
            if ($to = $request->get('to')) {
                $q->whereDate('date', '<=', $to);
            }
            if ($warehouseId = $request->get('warehouse_id')) {
                $q->where('warehouse_id', $warehouseId);
            }
            if ($supplierId = $request->get('supplier_id')) {
                $q->where('supplier_id', $supplierId);
            }
            if ($destination = $request->get('destination')) {
                $q->where('destination', 'like', "%{$destination}%");
            }
        });

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }
    }

    protected function exportRows($rows, array $headings, string $filename, string $format, string $title): mixed
    {
        abort_unless(request()->user()?->can('reports.export'), 403, 'Tidak diizinkan export.');

        $export = new GenericTableExport(collect($rows), $headings);

        return match ($format) {
            'xlsx' => ExcelFacade::download($export, "{$filename}.xlsx"),
            'csv' => ExcelFacade::download($export, "{$filename}.csv", Excel::CSV),
            'pdf' => Pdf::loadView('reports.table-pdf', [
                'title' => $title,
                'headings' => $headings,
                'rows' => $rows,
            ])->download("{$filename}.pdf"),
            default => response()->json(['success' => false, 'message' => 'Format export tidak dikenal.'], 422),
        };
    }

    public function dashboardStats(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('dashboard.view'), 403);

        $today = now()->toDateString();

        $totalProducts = Product::count();
        $totalStock = (float) ProductStock::sum('quantity');
        $suppliers = Supplier::count();
        $warehouses = Warehouse::count();
        $inToday = (float) StockTransactionItem::whereHas('transaction', fn ($q) => $q->where('type', 'in')->whereDate('date', $today))->sum('quantity');
        $outToday = (float) StockTransactionItem::whereHas('transaction', fn ($q) => $q->where('type', 'out')->whereDate('date', $today))->sum('quantity');

        $lowStock = ProductStock::with(['product.category', 'warehouse'])
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->whereNull('products.deleted_at')
            ->whereColumn('product_stocks.quantity', '<=', 'products.minimum_stock')
            ->select('product_stocks.*')
            ->get()
            ->map(fn ($s) => [
                'barang' => $s->product->name,
                'sku' => $s->product->sku,
                'gudang' => $s->warehouse->name,
                'stok' => (float) $s->quantity,
                'minimum' => (float) $s->product->minimum_stock,
            ]);

        $outOfStockCount = ProductStock::join('products', 'products.id', '=', 'product_stocks.product_id')
            ->whereNull('products.deleted_at')
            ->where('product_stocks.quantity', '<=', 0)
            ->count();

        $recent = StockTransaction::with(['warehouse', 'creator'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($t) => [
                'nomor' => $t->number,
                'type' => $t->type,
                'user' => $t->creator?->name,
                'gudang' => $t->warehouse?->name,
                'created_at' => $t->created_at,
            ]);

        $chart = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i);

            return [
                'tanggal' => $date->format('d/m'),
                'masuk' => (float) StockTransactionItem::whereHas('transaction', fn ($q) => $q->where('type', 'in')->whereDate('date', $date->toDateString()))->sum('quantity'),
                'keluar' => (float) StockTransactionItem::whereHas('transaction', fn ($q) => $q->where('type', 'out')->whereDate('date', $date->toDateString()))->sum('quantity'),
            ];
        });

        $byCategory = Category::withCount('products')->get()->map(fn ($c) => ['name' => $c->name, 'jumlah' => $c->products_count]);

        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard.',
            'data' => [
                'stats' => [
                    'total_barang' => $totalProducts,
                    'total_stok' => $totalStock,
                    'supplier' => $suppliers,
                    'gudang' => $warehouses,
                    'masuk_hari_ini' => $inToday,
                    'keluar_hari_ini' => $outToday,
                    'stok_menipis' => $lowStock->count(),
                    'barang_habis' => $outOfStockCount,
                ],
                'low_stock' => $lowStock,
                'recent_activity' => $recent,
                'chart' => $chart,
                'by_category' => $byCategory,
            ],
        ]);
    }
}