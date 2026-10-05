<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog) {}

    /** @var class-string<Model> */
    protected string $modelClass;

    protected array $searchable = [];

    protected array $with = [];

    protected array $filters = [];

    protected int $defaultPerPage = 20;

    abstract protected function permissionPrefix(): string;

    abstract protected function rules(?int $id = null): array;

    public function index(Request $request): JsonResponse
    {
        $this->authorizePermission('view');

        $query = ($this->modelClass)::query()->with($this->with);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($this->filters as $filter) {
            if ($value = $request->get($filter)) {
                $query->where($filter, $value);
            }
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($sort = $request->get('sort')) {
            $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sort, $direction);
        } else {
            $query->latest();
        }

        $perPage = (int) $request->get('per_page', $this->defaultPerPage);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : $this->defaultPerPage;

        $paginator = $query->paginate($perPage);

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
        $this->authorizePermission('create');

        $data = $request->validate($this->rules());

        $record = ($this->modelClass)::create($this->beforeSave($data, null));

        $this->activityLog->log($request->user()?->id, 'create', $this->permissionPrefix(), $record->id, 'Membuat data baru');

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.',
            'data' => $record->load($this->with),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $this->authorizePermission('view');

        $record = ($this->modelClass)::with($this->with)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data.',
            'data' => $record,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('update');

        $record = ($this->modelClass)::findOrFail($id);

        $data = $request->validate($this->rules($id));

        $record->update($this->beforeSave($data, $record));

        $this->activityLog->log($request->user()?->id, 'update', $this->permissionPrefix(), $record->id, 'Memperbarui data');

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui.',
            'data' => $record->fresh($this->with),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorizePermission('delete');

        $record = ($this->modelClass)::findOrFail($id);
        $record->delete();

        $this->activityLog->log(request()->user()?->id, 'delete', $this->permissionPrefix(), $id, 'Menghapus data');

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus.',
            'data' => null,
        ]);
    }

    protected function beforeSave(array $data, ?Model $record): array
    {
        return $data;
    }

    protected function authorizePermission(string $action): void
    {
        abort_unless(
            request()->user()?->can($this->permissionPrefix().'.'.$action),
            403,
            'Anda tidak memiliki akses untuk aksi ini.'
        );
    }
}
