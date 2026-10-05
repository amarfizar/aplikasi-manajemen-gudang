<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransaction;
use App\Services\ActivityLogService;
use App\Services\InventoryService;
use App\Services\TransactionNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryService $inventory,
        protected TransactionNumberGenerator $numbers,
        protected ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('stock_adjustments.view'), 403);

        $query = StockTransaction::with(['warehouse', 'creator', 'items.product'])
            ->where('type', 'adjustment')
            ->latest();

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
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

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('stock_adjustments.create'), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'exists:products,id'],
            'change' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($request, $data) {
                $result = $this->inventory->adjustStock($data['product_id'], $data['warehouse_id'], (float) $data['change']);

                $transaction = StockTransaction::create([
                    'number' => $this->numbers->next('adjustment'),
                    'type' => 'adjustment',
                    'date' => $data['date'],
                    'warehouse_id' => $data['warehouse_id'],
                    'note' => $data['reason'].(! empty($data['note']) ? ' — '.$data['note'] : ''),
                    'created_by' => $request->user()->id,
                ]);

                $transaction->items()->create([
                    'product_id' => $data['product_id'],
                    'quantity' => abs((float) $data['change']),
                    'stock_before' => $result['before'],
                    'stock_after' => $result['after'],
                    'note' => $data['note'] ?? null,
                ]);

                return $transaction;
            });
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $this->activityLog->log($request->user()->id, 'create', 'stock_adjustments', $transaction->id, 'Penyesuaian stok '.$transaction->number);

        return response()->json([
            'success' => true,
            'message' => 'Penyesuaian stok berhasil dicatat.',
            'data' => $transaction->load(['warehouse', 'items.product']),
        ], 201);
    }
}
