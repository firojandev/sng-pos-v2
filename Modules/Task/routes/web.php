<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\TaskController;
use Modules\Task\Http\Controllers\TaskReportController;

// Tasks belong to a company (a real company, or the Default Company for its
// admins and staff) whose plan includes Tasks — checked in the controller
// (Task::isAvailableTo), since the Default Company's people have no shop.
// Assigning to others and seeing everyone's: tasks.assign, tasks.manage.
Route::middleware(['auth'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    // HR → Reporting: attendance and tasks day by day, for task managers.
    Route::get('report', [TaskReportController::class, 'index'])->name('report');
    Route::get('report/export', [TaskReportController::class, 'export'])->name('report.export');
    Route::get('{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::put('{task}', [TaskController::class, 'update'])->name('update');
    Route::patch('{task}/status', [TaskController::class, 'status'])->name('status');
    Route::delete('{task}', [TaskController::class, 'destroy'])->name('destroy');
});
