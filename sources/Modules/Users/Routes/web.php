<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\MahasiswaController;
use Modules\Users\Http\Controllers\PegawaiController;
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

Route::prefix('users')->group(function () {
    Route::middleware(['web'])->group(function () {
        //Data Mahasiswa
        Route::prefix('mahasiswa')->group(function () {
            Route::get('/', [MahasiswaController::class, 'index'])->name('home.mahasiswa');
            Route::get('/listmahasiswa', [MahasiswaController::class, 'table_mahasiswa'])->name('home.listmahasiswa');
        });

        //Data Dosen
        Route::prefix('dosen')->group(function () {
            Route::get('/', [PegawaiController::class, 'index'])->name('home.dosen');
            Route::get('/listpegawai', [PegawaiController::class, 'table_pegawai'])->name('home.listpegawai');
        });
    });
});

