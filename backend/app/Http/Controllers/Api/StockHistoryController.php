<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\StockTransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('stock_histories.view'), 403);

        $query = StockTransactionItem::with(['product', 'transaction.warehouse', 'transaction.creator'])
            ->whereHas('transaction', function ($q) use ($request) {
                if ($type = $request->get('type')) {
                    $q->where('type', $type);
                }
                if ($warehouseId = $request->get('warehouse_id')) {
                    $q->where('warehouse_id', $warehouseId);
                }
                if ($userId = $request->get('user_id')) {
                    $q->where('created_by', $userId);
                }
                if ($from = $request->get('from')) {
                    $q->whereDate('date', '>=', $from);
                }
                if ($to = $request->get('to')) {
                    $q->whereDate('date', '<=', $to);
                }
            })
            ->orderByDesc('created_at');

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        $paginator = $query->paginate(20);

        $currentStocks = ProductStock::all()->keyBy(fn ($s) => $s->product_id.'-'.$s->warehouse_id);

        $running = [];
        $items = collect($paginator->items())->map(function ($item) use ($currentStocks, &$running) {
            $key = $item->product_id.'-'.$item->transaction->warehouse_id;
            $change = match ($item->transaction->type) {
                'in' => (float) $item->quantity,
                'out' => -(float) $item->quantity,
                'adjustment' => (float) ($item->stock_after - $item->stock_before),
                default => 0,
            };

            if (! array_key_exists($key, $running)) {
                $running[$key] = (float) ($currentStocks->get($key)?->quantity ?? 0);
            }

            // For 'in'/'out', stock_after is recorded; use it as saldo directly when available.
            $saldo = $item->stock_after !== null ? (float) $item->stock_after : $running[$key];
            $running[$key] = $saldo - $change;

            return [
                'id' => $item->id,
                'date' => $item->transaction->date,
                'number' => $item->transaction->number,
                'type' => $item->transaction->type,
                'product' => $item->product?->name,
                'product_id' => $item->product_id,
                'warehouse' => $item->transaction->warehouse?->name,
                'change' => $change,
                'saldo' => $saldo,
                'user' => $item->transaction->creator?->name,
                'note' => $item->note,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat stok.',
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
