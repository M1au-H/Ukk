<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\MenuOptionGroupController;
use App\Http\Controllers\Admin\MenuOptionValueController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\MenuController as CustomerMenuController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use Illuminate\Support\Facades\Route;

// ==== CUSTOMER (PUBLIC, TANPA LOGIN) ====

Route::get('/menu/resolve/{table_token}', [CustomerMenuController::class, 'resolveTable']);
Route::get('/categories', [CustomerMenuController::class, 'categories']);
Route::get('/menus', [CustomerMenuController::class, 'menus']);
Route::get('/menus/{menu}', [CustomerMenuController::class, 'showMenu']);

// throttle:5,1 = maks 5 request per menit per IP. Proteksi kasar terhadap spam order;
// bisa diperhalus nanti kalau perlu limit lebih ketat per session_token.
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:5,1');
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);

Route::post('/payments/midtrans/callback', [PaymentController::class, 'callback']);

// ==== AUTH (Admin & Kitchen) ====

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ==== ADMIN ONLY ====
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::apiResource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate-token', [TableController::class, 'regenerateToken']);

        Route::apiResource('menus', AdminMenuController::class);
        Route::patch('menus/{menu}/toggle-availability', [AdminMenuController::class, 'toggleAvailability']);

        Route::get('menus/{menu}/option-groups', [MenuOptionGroupController::class, 'index']);
        Route::post('menus/{menu}/option-groups', [MenuOptionGroupController::class, 'store']);
        Route::put('option-groups/{optionGroup}', [MenuOptionGroupController::class, 'update']);
        Route::delete('option-groups/{optionGroup}', [MenuOptionGroupController::class, 'destroy']);

        Route::post('option-groups/{optionGroup}/values', [MenuOptionValueController::class, 'store']);
        Route::put('option-values/{value}', [MenuOptionValueController::class, 'update']);
        Route::delete('option-values/{value}', [MenuOptionValueController::class, 'destroy']);

        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
    });
});
