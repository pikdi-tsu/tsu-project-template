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
        $superAdminModule = 'super admin ' . config('app.module.name');
        Gate::before(static function ($user, $ability) use ($superAdminModule){
            return $user->hasRole('Super Admin|'.$superAdminModule) ? true : null;
        });
    }
}
