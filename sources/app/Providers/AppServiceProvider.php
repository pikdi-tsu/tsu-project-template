<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\System\Models\MenuSidebar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Gate::before(static function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        View::composer('layouts.sidebar', function ($view) {

            // Ambil Menu Hierarki (Parent include Children)
            $menus = MenuSidebar::with('children')
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();

            $user = Auth::user();

            // Daftar Role Prioritas
            $roleDewa = ['super admin', 'admin', 'user'];

            // Route Target untuk Mahasiswa
            $targetRoute = 'profile.change-password';

            // 🛡️ LOGIC FILTER CERDAS
            if ($user && !$user->hasAnyRole($roleDewa)) {

                // Kita filter daftar Parent-nya
                $menus = $menus->filter(function ($parent) use ($targetRoute) {

                    // Cek 1: Apakah parent ini punya anak yang route-nya 'profile.change-password'?
                    $targetChild = $parent->children->firstWhere('route', $targetRoute);

                    if ($targetChild) {
                        // KETEMU! Parent ini adalah bapaknya target.

                        // TAPI TUNGGU! Kita harus buang saudara-saudaranya.
                        // Kita timpa relation 'children' parent ini biar isinya CUMA si target.
                        $parent->setRelation('children', collect([$targetChild]));

                        return true; // Simpan Parent ini
                    }

                    // Cek 2: Atau jangan-jangan Parent ini SENDIRI adalah targetnya? (Jaga-jaga)
                    if ($parent->route === $targetRoute) {
                        return true;
                    }

                    // Sisanya (Dashboard, KRS, dll) tendang!
                    return false;
                });
            }

            $view->with('menus', $menus);
        });
    }
}
