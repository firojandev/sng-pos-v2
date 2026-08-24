<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth', 'permission:users', 'feature:users'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});
