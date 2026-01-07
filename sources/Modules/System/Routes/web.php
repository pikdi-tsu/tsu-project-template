<?php

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\DashboardController;
use Modules\System\Http\Controllers\HomeController;
use Modules\System\Http\Controllers\LoginController;
use Modules\System\Http\Controllers\SettingController;
use App\Http\Controllers\SsoController;
use Modules\System\Http\Controllers\MenuController;
use Modules\System\Http\Controllers\UserProfileController;

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

Route::prefix('')->group(function() {
    Route::get('/', [HomeController::class, 'index'])->name('indexing')->middleware('web', 'guest');
    Route::middleware(['web'])->group(function () {
        Route::get('login', [LoginController::class, 'index'])->name('login')->middleware('guest');
        Route::post('login', [LoginController::class, 'login'])->name('login.action');
        Route::get('login/sso', [SsoController::class, 'redirect'])->name('sso.login');
        Route::get('login/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
//        Route::get('NewPassword', [LoginController::class, 'newPassword'])->name('NewPassword');
//        Route::post('NewPasswordAction', [LoginController::class, 'newPasswordAction'])->name('NewPasswordAction');
//        Route::get('checkbirthday', [LoginController::class, 'checkbirthday']);
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        //forgot password
//        Route::get('forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot_password')->middleware('guest');
//        Route::post('forgot-password', [LoginController::class, 'actionSendLink'])->name('forgot_password.send');
//        Route::get('reset-password', [LoginController::class, 'showResetForm'])->name('forgot_password.form_reset')->middleware('guest');
//        Route::post('reset-password', [LoginController::class, 'updatePassword'])->name('forgot_password.action')->middleware('guest');
//        Route::get('/form_ForgotPassword/{params}', [LoginController::class, 'FormForgotPassword'])->name('ForgotPassword.formreset');
//        Route::post('/Action_ForgotPassword/{params}', [LoginController::class, 'ForgotPasswordAction'])->name('ForgotPassword.ActionReset');

        // Menu
        Route::prefix('system')->middleware(['web', 'auth'])->name('system.')->group(function() {
            Route::get('menu/json', [MenuController::class, 'datatable'])->name('menu.json');
            Route::resource('menu', MenuController::class);
        });

        // Profile & Password
        Route::prefix('profile')->middleware(['web', 'auth'])->group(function() {
            Route::get('/', [UserProfileController::class, 'index'])->name('profile');
            Route::post('/profile/photo', [UserProfileController::class, 'updatePhoto'])->name('save.change-profile');
            Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.update-password');
        });

        //Setting
        Route::prefix('setting')->group(function(){
            //Change Password
//            Route::get('/changepassword', [SettingController::class, 'showChangePassword'])->name('show.changepassword');
//            Route::post('/changepasswordsave', [SettingController::class, 'saveChangePassword'])->name('save.changepassword');


            //User Management
            Route::get('/usermanagement', [SettingController::class, 'userManagement'])->name('show.userManagement');
            Route::get('/tabelPegawai', [SettingController::class, 'table_pegawai'])->name('show.tabelPegawai');
            Route::get('/tabelMahasiswa', [SettingController::class, 'table_mahasiswa'])->name('show.tabelMahasiswa');
            Route::get('/finduser', [SettingController::class, 'searchNama'])->name('show.finduser');
            Route::post('/StoreUser', [SettingController::class, 'StoreUser'])->name('show.saveUser');
            Route::get('/detailuser/{params}', [SettingController::class, 'DetailUser'])->name('show.detailuser');
            Route::get('/deleteuser/{params}', [SettingController::class, 'DeleteUser'])->name('show.deleteuser');

            //User Reset
            Route::get('/userreset', [SettingController::class, 'UserReset'])->name('UserReset.show');
            Route::get('/userreset_tabelPegawai', [SettingController::class, 'UserReset_TablePegawai'])->name('UserReset.tabelPegawai');
            Route::get('/userreset_tabelMahasiswa', [SettingController::class, 'UserReset_TableMahasiswa'])->name('UserReset.tabelMahasiswa');
            Route::get('/ResetPassword/{params}', [SettingController::class, 'ResetPassword'])->name('UserReset.ResetPassword');
            Route::get('/ResetQA/{params}', [SettingController::class, 'ResetQA'])->name('UserReset.ResetQA');

            //List Menu
            Route::get('/ShowMenu', [SettingController::class, 'ShowMenu'])->name('menu.show');
            Route::get('/LisMenu', [SettingController::class, 'table_menu'])->name('menu.TabelMenu');
            Route::post('/SaveUpdateMenu', [SettingController::class, 'SaveUpdateMenu'])->name('menu.SaveMenu');
            Route::get('/GetMenu/{params}', [SettingController::class, 'GetMenu'])->name('menu.GetMenu');
            Route::get('/DeleteAktif/{params1}/{params2}', [SettingController::class, 'DeleteMenu'])->name('menu.DeleteAktif');

            //Group User
            Route::get('/ShowGroupUser', [SettingController::class, 'ShowGroupUser'])->name('gruopuser.show');
            Route::get('/LisGroupUser', [SettingController::class, 'table_groupuser'])->name('gruopuser.TabelGroupUser');
            Route::post('/SaveUpdateGroupUser', [SettingController::class, 'SaveUpdateGroupUser'])->name('gruopuser.Save');
            Route::get('/GetGroupUser/{params}', [SettingController::class, 'GetGroupUser'])->name('gruopuser.GetGroupUser');
            Route::get('/ShowPrivilege/{params}', [SettingController::class, 'ShowPrivilege'])->name('gruopuser.ShowPrivilege');
            Route::post('/SavePrivilege/{params}', [SettingController::class, 'StorePrivilege'])->name('gruopuser.SavePrivilege');
        });
    });
});
