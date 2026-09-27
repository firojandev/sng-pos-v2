<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\TaskController;

// Everyone manages their own tasks; assigning to others and seeing
// everyone's are checked in the controller (tasks.assign, tasks.manage).
Route::middleware(['auth', 'feature:tasks'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::get('{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::put('{task}', [TaskController::class, 'update'])->name('update');
    Route::patch('{task}/status', [TaskController::class, 'status'])->name('status');
    Route::delete('{task}', [TaskController::class, 'destroy'])->name('destroy');
});
