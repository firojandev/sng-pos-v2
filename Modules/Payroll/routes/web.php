<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\EmployeeLoanController;
use Modules\Payroll\Http\Controllers\EmployeeSalaryController;
use Modules\Payroll\Http\Controllers\FinalSettlementController;
use Modules\Payroll\Http\Controllers\MySalaryController;
use Modules\Payroll\Http\Controllers\PayrollRunController;
use Modules\Payroll\Http\Controllers\PayrollSetupController;
use Modules\Payroll\Http\Controllers\StatutoryPaymentController;
use Modules\Payroll\Http\Controllers\TaxSetupController;

Route::middleware(['auth', 'feature:payroll'])->prefix('hr/payroll')->name('payroll.')->group(function () {
    Route::prefix('runs')->name('runs.')->group(function () {
        Route::get('/', [PayrollRunController::class, 'index'])->name('index')->middleware('permission:payroll.view');
        Route::post('/', [PayrollRunController::class, 'store'])->name('store')->middleware('permission:payroll.create');
        Route::get('{run}', [PayrollRunController::class, 'show'])->name('show')->middleware('permission:payroll.view');
        Route::get('{run}/print', [PayrollRunController::class, 'printAll'])->name('print')->middleware('permission:payroll.view');
        Route::post('{run}/recalculate', [PayrollRunController::class, 'recalculate'])->name('recalculate')->middleware('permission:payroll.edit');
        Route::post('{run}/approve', [PayrollRunController::class, 'approve'])->name('approve')->middleware('permission:payroll.approve');
        Route::post('{run}/reopen', [PayrollRunController::class, 'reopen'])->name('reopen')->middleware('permission:payroll.approve');
        Route::post('{run}/pay-all', [PayrollRunController::class, 'payAll'])->name('pay-all')->middleware('permission:payroll.payment');
        Route::delete('{run}', [PayrollRunController::class, 'destroy'])->name('destroy')->middleware('permission:payroll.delete');
    });

    Route::prefix('payslips')->name('payslips.')->group(function () {
        Route::get('{payslip}', [PayrollRunController::class, 'payslip'])->name('show')->middleware('permission:payroll.view');
        Route::put('{payslip}', [PayrollRunController::class, 'adjust'])->name('adjust')->middleware('permission:payroll.edit');
        Route::post('{payslip}/pay', [PayrollRunController::class, 'pay'])->name('pay')->middleware('permission:payroll.payment');
    });

    Route::delete('payments/{payment}', [PayrollRunController::class, 'destroyPayment'])->name('payments.destroy')->middleware('permission:payroll.payment');

    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [EmployeeLoanController::class, 'index'])->name('index')->middleware('permission:payroll.view');
        Route::post('/', [EmployeeLoanController::class, 'store'])->name('store')->middleware('permission:payroll.create');
        Route::post('{loan}/repay', [EmployeeLoanController::class, 'repay'])->name('repay')->middleware('permission:payroll.payment');
        Route::delete('{loan}', [EmployeeLoanController::class, 'destroy'])->name('destroy')->middleware('permission:payroll.delete');
    });

    Route::prefix('settlements')->name('settlements.')->group(function () {
        Route::get('/', [FinalSettlementController::class, 'index'])->name('index')->middleware('permission:payroll.view');
        Route::post('/', [FinalSettlementController::class, 'store'])->name('store')->middleware('permission:payroll.create');
        Route::get('{settlement}', [FinalSettlementController::class, 'show'])->name('show')->middleware('permission:payroll.view');
        Route::put('{settlement}', [FinalSettlementController::class, 'update'])->name('update')->middleware('permission:payroll.edit');
        Route::post('{settlement}/finalize', [FinalSettlementController::class, 'finalize'])->name('finalize')->middleware('permission:payroll.approve');
        Route::post('{settlement}/reopen', [FinalSettlementController::class, 'reopen'])->name('reopen')->middleware('permission:payroll.approve');
        Route::post('{settlement}/pay', [FinalSettlementController::class, 'pay'])->name('pay')->middleware('permission:payroll.payment');
        Route::delete('{settlement}', [FinalSettlementController::class, 'destroy'])->name('destroy')->middleware('permission:payroll.delete');
    });

    Route::prefix('tax')->name('tax.')->group(function () {
        Route::get('/', [TaxSetupController::class, 'index'])->name('index')->middleware('permission:payroll.view');
        Route::put('{taxYear}/employees/{employee}', [TaxSetupController::class, 'updateDeclaration'])->name('declarations.update')->middleware('permission:payroll.edit');
        Route::post('{taxYear}/close', [TaxSetupController::class, 'closeYear'])->name('close')->middleware('permission:payroll.approve');
    });

    Route::prefix('statutory')->name('statutory.')->group(function () {
        Route::get('/', [StatutoryPaymentController::class, 'index'])->name('index')->middleware('permission:payroll.view');
        Route::post('/', [StatutoryPaymentController::class, 'store'])->name('store')->middleware('permission:payroll.payment');
        Route::post('{payment}/pay', [StatutoryPaymentController::class, 'pay'])->name('pay')->middleware('permission:payroll.payment');
        Route::delete('{payment}', [StatutoryPaymentController::class, 'destroy'])->name('destroy')->middleware('permission:payroll.payment');
    });

    Route::get('employees/{employee}/salary', [EmployeeSalaryController::class, 'show'])->name('salary.show')->middleware('permission:payroll.view');
    Route::post('employees/{employee}/salary/revisions', [EmployeeSalaryController::class, 'storeRevision'])->name('salary.revisions.store')->middleware('permission:payroll.edit');
    Route::put('employees/{employee}/salary/structure', [EmployeeSalaryController::class, 'updateStructure'])->name('salary.structure.update')->middleware('permission:payroll.edit');
});

Route::middleware(['auth', 'feature:payroll-setup'])->prefix('hr/payroll/setup')->name('payroll-setup.')->group(function () {
    Route::get('/', [PayrollSetupController::class, 'index'])->name('index')->middleware('permission:payroll-setup.view');

    Route::middleware('permission:payroll-setup.edit')->group(function () {
        Route::put('settings', [PayrollSetupController::class, 'updateSettings'])->name('settings.update');
        Route::post('components', [PayrollSetupController::class, 'storeComponent'])->name('components.store');
        Route::put('components/{component}', [PayrollSetupController::class, 'updateComponent'])->name('components.update');
        Route::delete('components/{component}', [PayrollSetupController::class, 'destroyComponent'])->name('components.destroy');
        Route::post('tax-years', [TaxSetupController::class, 'saveTaxYear'])->name('tax-years.store');
        Route::put('tax-years/{taxYear}', [TaxSetupController::class, 'saveTaxYear'])->name('tax-years.update');
    });
});

// My Salary: a user linked to an employee sees their own salary, payslips
// and payments.
Route::middleware(['auth', 'feature:payroll'])->prefix('my/salary')->name('my.salary.')->group(function () {
    Route::get('/', [MySalaryController::class, 'index'])->name('index');
    Route::get('payslips/{payslip}', [MySalaryController::class, 'payslip'])->name('payslip')->whereNumber('payslip');
});
