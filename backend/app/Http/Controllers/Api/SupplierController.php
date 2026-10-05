<?php

namespace App\Http\Controllers\Api;

use App\Models\Supplier;

class SupplierController extends CrudController
{
    protected string $modelClass = Supplier::class;

    protected array $searchable = ['name', 'code', 'email', 'phone'];

    protected array $filters = ['status'];

    protected function permissionPrefix(): string
    {
        return 'suppliers';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', "unique:suppliers,code,{$id}"],
            'name' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'note' => ['nullable', 'string'],
        ];
    }
}
