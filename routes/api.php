<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\MenuImageController;
use App\Http\Controllers\Admin\MenuOptionGroupController;
use App\Http\Controllers\Admin\MenuOptionValueController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Kitchen\OrderController as KitchenOrderController;
use Illuminate\Support\Facades\Route;

// ==== CUSTOMER (PUBLIK, TANPA LOGIN) ====

Route::get('/menu/resolve/{table_token}', [CustomerMenuController::class, 'resolveTable']);
Route::get('/categories', [CustomerMenuController::class, 'categories']);
Route::get('/menus', [CustomerMenuController::class, 'menus']);
Route::get('/menus/{menu}', [CustomerMenuController::class, 'showMenu']);

Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::post('/orders/{order}/sync-payment', [PaymentController::class, 'sync'])->middleware('throttle:60,1');

Route::post('/payments/midtrans/callback', [PaymentController::class, 'callback']);

// ==== AUTH (Admin & Kitchen) ====

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ==== ADMIN SAJA ====
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::apiResource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate-token', [TableController::class, 'regenerateToken']);

        Route::apiResource('menus', AdminMenuController::class);
        Route::patch('menus/{menu}/toggle-availability', [AdminMenuController::class, 'toggleAvailability']);
        Route::post('menus/{menu}/image', [MenuImageController::class, 'store']);
        Route::delete('menus/{menu}/image', [MenuImageController::class, 'destroy']);

        Route::get('menus/{menu}/option-groups', [MenuOptionGroupController::class, 'index']);
        Route::post('menus/{menu}/option-groups', [MenuOptionGroupController::class, 'store']);
        Route::put('option-groups/{optionGroup}', [MenuOptionGroupController::class, 'update']);
        Route::delete('option-groups/{optionGroup}', [MenuOptionGroupController::class, 'destroy']);

        Route::post('option-groups/{optionGroup}/values', [MenuOptionValueController::class, 'store']);
        Route::put('option-values/{value}', [MenuOptionValueController::class, 'update']);
        Route::delete('option-values/{value}', [MenuOptionValueController::class, 'destroy']);

        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('orders/{order}/cancel', [AdminOrderController::class, 'cancel']);

        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
    });

    // ==== KITCHEN SAJA ====
    Route::middleware('role:kitchen')->prefix('kitchen')->group(function () {
        Route::get('orders', [KitchenOrderController::class, 'index']);
        Route::get('orders/{order}', [KitchenOrderController::class, 'show']);
        Route::patch('orders/{order}/status', [KitchenOrderController::class, 'updateStatus']);
    });
});
