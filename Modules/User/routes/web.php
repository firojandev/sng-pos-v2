<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\RoleController;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth', 'feature:users'])->group(function () {
    Route::resource('users', UserController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:users.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:users.write')
        ->middlewareFor(['destroy'], 'permission:users.delete');
    Route::resource('roles', RoleController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:users.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:users.write')
        ->middlewareFor(['destroy'], 'permission:users.delete');
});
