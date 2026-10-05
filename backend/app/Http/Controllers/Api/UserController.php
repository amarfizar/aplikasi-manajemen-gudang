<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('users.view'), 403);

        $query = User::with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dimuat.',
            'data' => collect($paginator->items())->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'status' => $u->status,
                'role' => $u->getRoleNames()->first(),
                'last_login_at' => $u->last_login_at,
                'created_at' => $u->created_at,
            ]),
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
        abort_unless($request->user()?->can('users.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
        ]);
        $user->assignRole($data['role']);

        $this->activityLog->log($request->user()->id, 'create', 'users', $user->id, 'Membuat user '.$user->email);

        return response()->json(['success' => true, 'message' => 'User dibuat.', 'data' => $user], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('users.update'), 403);

        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', "unique:users,email,{$id}"],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            ...(isset($data['password']) && $data['password'] ? ['password' => Hash::make($data['password'])] : []),
        ]);
        $user->syncRoles([$data['role']]);

        $this->activityLog->log($request->user()->id, 'update', 'users', $user->id, 'Memperbarui user '.$user->email);

        return response()->json(['success' => true, 'message' => 'User diperbarui.', 'data' => $user]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        abort_unless($request->user()?->can('users.delete'), 403);

        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $email = $user->email;
        $user->delete();

        $this->activityLog->log($request->user()->id, 'delete', 'users', $id, 'Menghapus user '.$email);

        return response()->json(['success' => true, 'message' => 'User dihapus.', 'data' => null]);
    }
}
