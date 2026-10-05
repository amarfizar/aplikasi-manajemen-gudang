<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\StockTransaction;
use App\Services\ActivityLogService;
use App\Services\InventoryService;
use App\Services\TransactionNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockOpnameController extends Controller
{
    public function __construct(
        protected InventoryService $inventory,
        protected TransactionNumberGenerator $numbers,
        protected ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.view'), 403);

        $query = StockOpname::with(['warehouse', 'creator', 'approver'])->latest();

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dimuat.',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.view'), 403);

        $opname = StockOpname::with(['warehouse', 'creator', 'approver', 'items.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail stock opname.',
            'data' => $opname,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.create'), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'pic' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        $opname = DB::transaction(function () use ($request, $data) {
            $opname = StockOpname::create([
                ...$data,
                'number' => $this->numbers->next('opname'),
                'status' => 'counting',
                'created_by' => $request->user()->id,
            ]);

            $this->activityLog->log($request->user()->id, 'create', 'stock_opname', $opname->id, 'Membuat opname '.$opname->number);

            $stocks = ProductStock::where('warehouse_id', $data['warehouse_id'])->get();
            foreach ($stocks as $stock) {
                $opname->items()->create([
                    'product_id' => $stock->product_id,
                    'system_stock' => $stock->quantity,
                ]);
            }

            return $opname;
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock opname dibuat. Mulai hitung stok fisik.',
            'data' => $opname->load(['warehouse', 'items.product']),
        ], 201);
    }

    public function updateItems(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.create'), 403);

        $opname = StockOpname::findOrFail($id);

        if ($opname->status !== 'counting') {
            return response()->json(['success' => false, 'message' => 'Opname tidak dalam status counting.'], 422);
        }

        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:stock_opname_items,id'],
            'items.*.physical_stock' => ['required', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($opname, $data) {
            foreach ($data['items'] as $item) {
                $opnameItem = StockOpnameItem::where('stock_opname_id', $opname->id)->findOrFail($item['id']);
                $opnameItem->update([
                    'physical_stock' => $item['physical_stock'],
                    'difference' => (float) $item['physical_stock'] - (float) $opnameItem->system_stock,
                    'note' => $item['note'] ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Stok fisik tersimpan.',
            'data' => $opname->load('items.product'),
        ]);
    }

    public function submitForReview(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.create'), 403);

        $opname = StockOpname::findOrFail($id);

        if ($opname->status !== 'counting') {
            return response()->json(['success' => false, 'message' => 'Status tidak valid.'], 422);
        }

        if ($opname->items()->whereNull('physical_stock')->exists()) {
            return response()->json(['success' => false, 'message' => 'Semua barang harus diisi stok fisiknya.'], 422);
        }

        $opname->update(['status' => 'review']);

        return response()->json(['success' => true, 'message' => 'Opname masuk tahap review.', 'data' => $opname]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.approve'), 403);

        $opname = StockOpname::with('items')->findOrFail($id);

        if ($opname->status !== 'review') {
            return response()->json(['success' => false, 'message' => 'Opname harus dalam status review.'], 422);
        }

        try {
            DB::transaction(function () use ($request, $opname) {
                $adjustmentTx = StockTransaction::create([
                    'number' => $this->numbers->next('adjustment'),
                    'type' => 'adjustment',
                    'date' => now()->toDateString(),
                    'warehouse_id' => $opname->warehouse_id,
                    'note' => 'Penyesuaian dari stock opname '.$opname->number,
                    'created_by' => $request->user()->id,
                ]);

                foreach ($opname->items as $item) {
                    $diff = (float) $item->difference;
                    if ($diff == 0.0) {
                        continue;
                    }

                    $result = $this->inventory->adjustStock($item->product_id, $opname->warehouse_id, $diff);

                    $adjustmentTx->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => abs($diff),
                        'stock_before' => $result['before'],
                        'stock_after' => $result['after'],
                        'note' => $item->note,
                    ]);
                }

                $opname->update([
                    'status' => 'approved',
                    'approved_by' => $request->user()->id,
                    'approved_at' => now(),
                ]);
            });
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $this->activityLog->log($request->user()->id, 'approve', 'stock_opname', $opname->id, 'Approve opname '.$opname->number);

        return response()->json([
            'success' => true,
            'message' => 'Stock opname disetujui. Stok berhasil disesuaikan.',
            'data' => $opname->fresh(['items.product', 'approver']),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('stock_opname.create'), 403);

        $opname = StockOpname::findOrFail($id);

        if ($opname->status === 'approved') {
            return response()->json(['success' => false, 'message' => 'Opname yang sudah disetujui tidak dapat dibatalkan.'], 422);
        }

        $opname->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'message' => 'Opname dibatalkan.', 'data' => $opname]);
    }
}
