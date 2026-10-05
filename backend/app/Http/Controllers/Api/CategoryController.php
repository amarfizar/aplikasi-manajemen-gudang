<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends CrudController
{
    protected string $modelClass = Category::class;

    protected array $searchable = ['name', 'code'];

    protected array $filters = ['status'];

    protected function permissionPrefix(): string
    {
        return 'categories';
    }

    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', "unique:categories,code,{$id}"],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
