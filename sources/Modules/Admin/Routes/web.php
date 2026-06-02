<?php

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

use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\DataKaryawanController;
use Modules\System\Http\Middleware\CheckAdminRole;

// Aktifkan CheckAdminRole::class di middleware jika ada dashboard users sendiri
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- ROUTE DATA KARYAWAN ---
    Route::prefix('data-karyawan')->name('data-karyawan.')->middleware(['permission:admin:data-karyawan:view'])->group(function () {
        // Route JSON
        Route::get('/json', [DataKaryawanController::class, 'datatable'])->name('json');

        // Route CRUD
        Route::resource('/', DataKaryawanController::class)->parameters(['' => 'id']);
        Route::post('/{id}/bio-aktif', [DataKaryawanController::class, 'bioAktif'])->name('bio-aktif');
    });
});
