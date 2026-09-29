<?php

use Illuminate\Support\Facades\Route;
use Modules\Company\Http\Controllers\CompanyController;
use Modules\Company\Http\Controllers\CompanyRoleController;
use Modules\Company\Http\Controllers\CompanySettingsController;
use Modules\Company\Http\Controllers\CompanyShopAdminController;
use Modules\Company\Http\Controllers\CompanyShopController;
use Modules\Company\Http\Controllers\CompanyUserController;
use Modules\Company\Http\Controllers\DefaultCompanyController;
use Modules\Company\Http\Controllers\ShopCompanyController;

Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    Route::resource('companies', CompanyController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::post('companies/{company}/admins', [CompanyController::class, 'storeAdmin'])->name('companies.admins.store');
    Route::put('companies/{company}/plan', [CompanyController::class, 'updatePlan'])->name('companies.plan.update');
    Route::delete('companies/{company}/admins/{user}', [CompanyController::class, 'destroyAdmin'])->name('companies.admins.destroy');
    Route::put('shops/{shop}/company', [ShopCompanyController::class, 'update'])->name('shops.company.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('default-company', [DefaultCompanyController::class, 'index'])->name('default-company.index');
    Route::post('default-company/shops/{shop}/open', [DefaultCompanyController::class, 'open'])->name('default-company.shops.open');

    Route::get('company/settings', [CompanySettingsController::class, 'edit'])->name('company-settings.edit');
    Route::put('company/settings', [CompanySettingsController::class, 'update'])->name('company-settings.update');
    Route::post('company/shops', [CompanyShopController::class, 'store'])->name('company.shops.store');
    Route::get('company/users', [CompanyUserController::class, 'index'])->name('company.users.index');
    Route::post('company/users', [CompanyUserController::class, 'store'])->name('company.users.store');
    Route::put('company/users/{user}', [CompanyUserController::class, 'update'])->name('company.users.update');
    Route::delete('company/users/{user}', [CompanyUserController::class, 'destroy'])->name('company.users.destroy');
    Route::resource('company/roles', CompanyRoleController::class)->except(['show'])->names('company.roles')->parameters(['roles' => 'role']);
    Route::post('company/shop-admins', [CompanyShopAdminController::class, 'store'])->name('company.shop-admins.store');
    Route::delete('company/shops/{shop}/admins/{user}', [CompanyShopAdminController::class, 'destroy'])->name('company.shop-admins.destroy');
});
