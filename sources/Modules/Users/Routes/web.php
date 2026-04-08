<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\MahasiswaController;
use Modules\Users\Http\Controllers\PegawaiController;
use Modules\Users\Http\Controllers\UserController;
use Modules\Users\Http\Controllers\UserProfileController;

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
    // User
    Route::middleware(['permission:users:user:view'])->group(function() {
        Route::get('users/json', [UserController::class, 'datatable'])->name('user.json');
        Route::post('user/sync', [UserController::class, 'sync'])->name('user.sync'); // Route Sync
        Route::resource('user', UserController::class);
    });

});

// Profile & Password
Route::prefix('profile')->middleware(['auth'])->name('profile.')->group(function() {
    Route::get('/', [UserProfileController::class, 'index'])->name('index');
    Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('update-password');
});
