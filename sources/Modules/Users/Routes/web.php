<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\MahasiswaController;
use Modules\Users\Http\Controllers\PegawaiController;
use Modules\Users\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('users')->name('users.')->middleware(['auth'])->group(function () {
    Route::middleware(['permission:system:user:view'])->group(function() {
        Route::get('users/json', [UserController::class, 'datatable'])->name('user.json');
        Route::post('user/sync', [UserController::class, 'sync'])->name('user.sync'); // Route Sync
        Route::resource('user', UserController::class);
    });
});
