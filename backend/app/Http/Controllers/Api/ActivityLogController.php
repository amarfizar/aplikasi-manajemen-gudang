<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('activity_logs.view'), 403);

        $query = ActivityLog::with('user')->latest();

        if ($module = $request->get('module')) {
            $query->where('module', $module);
        }
        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($search = $request->get('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $paginator = $query->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dimuat.',
            'data' => collect($paginator->items())->map(fn ($l) => [
                'id' => $l->id,
                'user' => $l->user?->name,
                'action' => $l->action,
                'module' => $l->module,
                'record_id' => $l->record_id,
                'description' => $l->description,
                'ip_address' => $l->ip_address,
                'user_agent' => $l->user_agent,
                'created_at' => $l->created_at,
            ]),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
