<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\AccountingReportController;
use Modules\Accounting\Http\Controllers\AccountingSetupController;
use Modules\Accounting\Http\Controllers\JournalEntryController;
use Modules\Accounting\Http\Controllers\LedgerAccountController;
use Modules\Accounting\Http\Middleware\EnsureAccountingLevel;

Route::middleware(['auth', 'feature:accounting', EnsureAccountingLevel::class])->prefix('accounting')->group(function () {
    Route::resource('ledger-accounts', LedgerAccountController::class)->except(['show', 'destroy'])
        ->middlewareFor(['index'], 'permission:accounting.view')
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:accounting.edit');

    Route::resource('journal-entries', JournalEntryController::class)->only(['index', 'create', 'store', 'show'])
        ->middlewareFor(['index', 'show'], 'permission:accounting.view')
        ->middlewareFor(['create', 'store'], 'permission:accounting.create');
    Route::post('journal-entries/{journal_entry}/reverse', [JournalEntryController::class, 'reverse'])
        ->name('journal-entries.reverse')
        ->middleware('permission:accounting.delete');

    Route::prefix('reports')->name('accounting-reports.')->middleware('permission:accounting.view')->group(function () {
        Route::get('trial-balance', [AccountingReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('general-ledger', [AccountingReportController::class, 'generalLedger'])->name('general-ledger');
        Route::get('profit-loss', [AccountingReportController::class, 'profitAndLoss'])->name('profit-loss');
        Route::get('balance-sheet', [AccountingReportController::class, 'balanceSheet'])->name('balance-sheet');
    });

    Route::prefix('setup')->name('accounting-setup.')->middleware('permission:accounting.edit')->group(function () {
        Route::get('/', [AccountingSetupController::class, 'index'])->name('index');
        Route::post('opening-balance', [AccountingSetupController::class, 'postOpening'])->name('opening-balance');
        Route::post('money-opening', [AccountingSetupController::class, 'saveMoneyOpening'])->name('money-opening');
        Route::post('fiscal-years', [AccountingSetupController::class, 'addNextYear'])->name('fiscal-years.store');
        Route::post('fiscal-years/{fiscalYear}/toggle', [AccountingSetupController::class, 'toggleYear'])->name('fiscal-years.toggle');
    });
});
