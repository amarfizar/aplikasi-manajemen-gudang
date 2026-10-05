<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockHistoryController;
use App\Http\Controllers\Api\StockInController;
use App\Http\Controllers\Api\StockOpnameController;
use App\Http\Controllers\Api\StockOutController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseLocationController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/settings/public', [SettingController::class, 'publicSettings']);

Route::middleware('api.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('locations', WarehouseLocationController::class);
    Route::get('products/next-sku', [ProductController::class, 'nextSku']);
    Route::get('products/import/template', [ProductController::class, 'importTemplate']);
    Route::post('products/import', [ProductController::class, 'import']);
    Route::apiResource('products', ProductController::class);

    Route::get('transactions/in', [StockInController::class, 'index']);
    Route::post('transactions/in', [StockInController::class, 'store']);
    Route::get('transactions/in/{id}', [StockInController::class, 'show']);
    Route::get('transactions/out', [StockOutController::class, 'index']);
    Route::post('transactions/out', [StockOutController::class, 'store']);
    Route::get('transactions/out/{id}', [StockOutController::class, 'show']);

    Route::get('reports/stock', [ReportController::class, 'stock']);
    Route::get('reports/stock-in', [ReportController::class, 'stockIn']);
    Route::get('reports/stock-out', [ReportController::class, 'stockOut']);
    Route::get('dashboard/stats', [ReportController::class, 'dashboardStats']);

    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/permissions', [RoleController::class, 'permissions']);
    Route::put('roles/{id}', [RoleController::class, 'update']);

    Route::get('activity-logs', [ActivityLogController::class, 'index']);

    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);

    Route::get('stock-histories', [StockHistoryController::class, 'index']);

    Route::get('stock-opnames', [StockOpnameController::class, 'index']);
    Route::post('stock-opnames', [StockOpnameController::class, 'store']);
    Route::get('stock-opnames/{id}', [StockOpnameController::class, 'show']);
    Route::put('stock-opnames/{id}/items', [StockOpnameController::class, 'updateItems']);
    Route::post('stock-opnames/{id}/review', [StockOpnameController::class, 'submitForReview']);
    Route::post('stock-opnames/{id}/approve', [StockOpnameController::class, 'approve']);
    Route::post('stock-opnames/{id}/cancel', [StockOpnameController::class, 'cancel']);

    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index']);
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store']);
});
