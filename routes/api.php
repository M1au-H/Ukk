<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuOptionGroupController;
use App\Http\Controllers\Admin\MenuOptionValueController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ==== ADMIN ONLY ====
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['show']);

        Route::apiResource('tables', TableController::class)->except(['show']);
        Route::post('tables/{table}/regenerate-token', [TableController::class, 'regenerateToken']);

        Route::apiResource('menus', MenuController::class);
        Route::patch('menus/{menu}/toggle-availability', [MenuController::class, 'toggleAvailability']);

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
