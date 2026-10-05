<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('roles.view'), 403);

        $roles = Role::with('permissions')->get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'permissions' => $r->permissions->pluck('name'),
        ]);

        return response()->json(['success' => true, 'message' => 'Daftar role.', 'data' => $roles]);
    }

    public function permissions(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('roles.view'), 403);

        return response()->json([
            'success' => true,
            'message' => 'Daftar permission.',
            'data' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('roles.manage'), 403);

        $role = Role::findOrFail($id);

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($data['permissions']);

        $this->activityLog->log($request->user()->id, 'update', 'roles', $role->id, 'Mengubah permission role '.$role->name);

        return response()->json(['success' => true, 'message' => 'Permission role diperbarui.', 'data' => $role->load('permissions')]);
    }
}
