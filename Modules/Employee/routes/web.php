<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\AttendanceController;
use Modules\Employee\Http\Controllers\AttendanceDeviceController;
use Modules\Employee\Http\Controllers\EmployeeController;
use Modules\Employee\Http\Controllers\EmployeeDocumentController;
use Modules\Employee\Http\Controllers\EmployeeProfileController;
use Modules\Employee\Http\Controllers\HrSetupController;
use Modules\Employee\Http\Controllers\LeaveRequestController;
use Modules\Employee\Http\Controllers\SelfServiceController;

Route::middleware(['auth', 'feature:employees'])->group(function () {
    Route::resource('employees', EmployeeController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:employees.view')
        ->middlewareFor(['create', 'store'], 'permission:employees.create')
        ->middlewareFor(['edit', 'update'], 'permission:employees.edit')
        ->middlewareFor(['destroy'], 'permission:employees.delete');

    Route::get('employees/{employee}/profile', [EmployeeProfileController::class, 'edit'])->name('employees.profile.edit')->middleware('permission:employees.view');
    Route::put('employees/{employee}/profile', [EmployeeProfileController::class, 'update'])->name('employees.profile.update')->middleware('permission:employees.edit');

    Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store')->middleware('permission:employees.edit');
    Route::get('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'download'])->name('employees.documents.download')->middleware('permission:employees.view');
    Route::delete('employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy')->middleware('permission:employees.edit');
});

Route::middleware(['auth', 'feature:attendance'])->prefix('hr/attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'sheet'])->name('sheet')->middleware('permission:attendance.view');
    Route::post('/', [AttendanceController::class, 'save'])->name('save')->middleware('permission:attendance.create');
    Route::post('process', [AttendanceController::class, 'process'])->name('process')->middleware('permission:attendance.edit');
    Route::post('clock/{employee}/{direction}', [AttendanceController::class, 'clock'])->whereIn('direction', ['in', 'out'])->name('clock')->middleware('permission:attendance.create');
    Route::get('monthly', [AttendanceController::class, 'monthly'])->name('monthly')->middleware('permission:attendance.view');

    Route::prefix('devices')->name('devices.')->middleware('permission:attendance.edit')->group(function () {
        Route::get('/', [AttendanceDeviceController::class, 'index'])->name('index');
        Route::post('/', [AttendanceDeviceController::class, 'store'])->name('store');
        Route::post('{device}/toggle', [AttendanceDeviceController::class, 'toggle'])->name('toggle');
        Route::delete('{device}', [AttendanceDeviceController::class, 'destroy'])->name('destroy');
        Route::post('import', [AttendanceDeviceController::class, 'import'])->name('import');
        Route::post('match', [AttendanceDeviceController::class, 'matchUnmatched'])->name('match');
    });
});

Route::middleware(['auth', 'feature:leave'])->prefix('hr/leave')->name('leave-requests.')->group(function () {
    Route::get('/', [LeaveRequestController::class, 'index'])->name('index')->middleware('permission:leave.view');
    Route::post('/', [LeaveRequestController::class, 'store'])->name('store')->middleware('permission:leave.create');
    Route::post('{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('approve')->middleware('permission:leave.approve');
    Route::post('{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('reject')->middleware('permission:leave.approve');
    Route::post('{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel')->middleware('permission:leave.approve');
    Route::get('{leaveRequest}/attachment', [LeaveRequestController::class, 'attachment'])->name('attachment');
});

Route::middleware(['auth', 'feature:hr-setup'])->prefix('hr/setup')->name('hr-setup.')->group(function () {
    Route::get('/', [HrSetupController::class, 'index'])->name('index')->middleware('permission:hr-setup.view');

    Route::middleware('permission:hr-setup.edit')->group(function () {
        Route::post('{kind}', [HrSetupController::class, 'storeNamed'])->whereIn('kind', ['departments', 'designations'])->name('named.store');
        Route::delete('{kind}/{id}', [HrSetupController::class, 'destroyNamed'])->whereIn('kind', ['departments', 'designations'])->name('named.destroy');
        Route::post('shifts/new', [HrSetupController::class, 'storeShift'])->name('shifts.store');
        Route::post('shifts/{shift}/default', [HrSetupController::class, 'makeDefaultShift'])->name('shifts.default');
        Route::delete('shifts/{shift}', [HrSetupController::class, 'destroyShift'])->name('shifts.destroy');
        Route::post('holidays/new', [HrSetupController::class, 'storeHoliday'])->name('holidays.store');
        Route::delete('holidays/{holiday}', [HrSetupController::class, 'destroyHoliday'])->name('holidays.destroy');
        Route::put('leave-types/{leaveType}', [HrSetupController::class, 'updateLeaveType'])->name('leave-types.update');
    });
});

// Employee self-service: a user linked to an employee.
Route::middleware('auth')->prefix('my')->name('my.')->group(function () {
    Route::post('clock', [SelfServiceController::class, 'clock'])->name('clock')->middleware('feature:attendance');
    Route::get('leave', [SelfServiceController::class, 'leave'])->name('leave.index')->middleware('feature:leave');
    Route::post('leave', [SelfServiceController::class, 'applyLeave'])->name('leave.store')->middleware('feature:leave');
    Route::post('leave/{leaveRequest}/cancel', [SelfServiceController::class, 'cancelLeave'])->name('leave.cancel')->middleware('feature:leave');
});
