<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\IclockController;

/*
 * Attendance machines (ZKTeco ADMS push). These paths are fixed by the
 * machines, and the requests carry no session or CSRF token.
 */
Route::get('iclock/cdata', [IclockController::class, 'handshake'])->name('iclock.handshake');
Route::post('iclock/cdata', [IclockController::class, 'upload'])->name('iclock.upload');
Route::get('iclock/getrequest', [IclockController::class, 'commands'])->name('iclock.commands');
Route::post('iclock/devicecmd', [IclockController::class, 'commands'])->name('iclock.device-command');
