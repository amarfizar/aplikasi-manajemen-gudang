<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'units.view', 'units.create', 'units.update', 'units.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.update', 'warehouses.delete',
            'locations.view', 'locations.create', 'locations.update', 'locations.delete',
            'transactions.in.view', 'transactions.in.create',
            'transactions.out.view', 'transactions.out.create',
            'stock_opname.view', 'stock_opname.create', 'stock_opname.approve',
            'stock_adjustments.view', 'stock_adjustments.create',
            'stock_histories.view',
            'reports.view', 'reports.export',
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.manage',
            'activity_logs.view',
            'settings.view', 'settings.update',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $adminGudang = Role::firstOrCreate(['name' => 'admin-gudang', 'guard_name' => 'web']);
        $adminGudang->syncPermissions(Permission::whereIn('name', [
            'dashboard.view',
            'products.view', 'products.create', 'products.update',
            'categories.view', 'categories.create', 'categories.update',
            'suppliers.view', 'suppliers.create', 'suppliers.update',
            'warehouses.view', 'locations.view', 'locations.create', 'locations.update',
            'transactions.in.view', 'transactions.in.create',
            'transactions.out.view', 'transactions.out.create',
            'stock_opname.view', 'stock_opname.create',
            'stock_adjustments.view', 'stock_adjustments.create',
            'stock_histories.view',
            'reports.view',
        ])->get());

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions(Permission::whereIn('name', [
            'dashboard.view',
            'products.view',
            'categories.view',
            'suppliers.view',
            'warehouses.view',
            'locations.view',
            'stock_histories.view',
            'reports.view',
        ])->get());

        $admin = User::firstOrCreate(
            ['email' => 'admin@inventory.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $admin->assignRole('super-admin');
    }
}
