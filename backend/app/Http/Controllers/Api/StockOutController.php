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

class StockOutController extends Controller
{
    public function __construct(
        protected InventoryService $inventory,
        protected TransactionNumberGenerator $numbers,
        protected ActivityLogService $activityLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('transactions.out.view'), 403);

        $query = StockTransaction::with(['warehouse', 'creator'])
            ->withCount('items')
            ->where('type', 'out')
            ->latest('date');

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('date', '<=', $to);
        }
        if ($search = $request->get('search')) {
            $query->where('number', 'like', "%{$search}%");
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
        abort_unless($request->user()?->can('transactions.out.view'), 403);

        $transaction = StockTransaction::with(['warehouse', 'creator', 'items.product', 'items.unit'])
            ->where('type', 'out')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail transaksi.',
            'data' => $transaction,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('transactions.out.create'), 403);

        $data = $request->validate([
            'date' => ['required', 'date'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'destination' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.note' => ['nullable', 'string'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($request, $data) {
                foreach ($data['items'] as $item) {
                    $available = $this->inventory->getCurrentStock((int) $item['product_id'], (int) $data['warehouse_id']);
                    if ((float) $item['quantity'] > $available) {
                        throw new RuntimeException('Stok tidak mencukupi untuk barang yang dipilih.');
                    }
                }

                $transaction = StockTransaction::create([
                    'number' => $this->numbers->next('out'),
                    'type' => 'out',
                    'date' => $data['date'],
                    'warehouse_id' => $data['warehouse_id'],
                    'destination' => $data['destination'] ?? null,
                    'department' => $data['department'] ?? null,
                    'pic' => $data['pic'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'note' => $data['note'] ?? null,
                    'created_by' => $request->user()->id,
                ]);

                foreach ($data['items'] as $item) {
                    $stock = $this->inventory->decreaseStock(
                        (int) $item['product_id'],
                        (int) $data['warehouse_id'],
                        (float) $item['quantity']
                    );

                    $transaction->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_id' => $item['unit_id'] ?? null,
                        'stock_after' => $stock->quantity,
                        'note' => $item['note'] ?? null,
                    ]);
                }

                return $transaction;
            });
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $this->activityLog->log($request->user()->id, 'create', 'transactions.out', $transaction->id, 'Barang keluar '.$transaction->number);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi barang keluar berhasil disimpan.',
            'data' => $transaction->load(['warehouse', 'items.product', 'items.unit']),
        ], 201);
    }
}
