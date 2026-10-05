<?php

namespace App\Http\Controllers\Api;

use App\Models\Unit;

class UnitController extends CrudController
{
    protected string $modelClass = Unit::class;

    protected array $searchable = ['name', 'symbol'];

    protected array $filters = ['status'];

    protected function permissionPrefix(): string
    {
        return 'units';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
