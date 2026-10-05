<?php

namespace App\Http\Controllers\Api;

use App\Models\WarehouseLocation;

class WarehouseLocationController extends CrudController
{
    protected string $modelClass = WarehouseLocation::class;

    protected array $searchable = ['name', 'code'];

    protected array $with = ['warehouse'];

    protected array $filters = ['status', 'warehouse_id'];

    protected function permissionPrefix(): string
    {
        return 'locations';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'code' => ['required', 'string', 'max:50', "unique:warehouse_locations,code,{$id},id,warehouse_id,".request()->get('warehouse_id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
