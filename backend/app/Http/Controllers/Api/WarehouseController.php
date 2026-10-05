<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;

class WarehouseController extends CrudController
{
    protected string $modelClass = Warehouse::class;

    protected array $searchable = ['name', 'code'];

    protected array $filters = ['status'];

    protected function permissionPrefix(): string
    {
        return 'warehouses';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', "unique:warehouses,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'pic' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
