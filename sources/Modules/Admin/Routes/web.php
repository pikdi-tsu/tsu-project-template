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

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    // URL: tsu-app.test/admin/dashboard
    // Name: route('admin.dashboard.index')
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
