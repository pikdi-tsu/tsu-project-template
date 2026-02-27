<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $superAdminModule = 'super admin ' . config('app.module.name');
        Gate::before(static function ($user, $ability) use ($superAdminModule) {
            if ($user->hasRole('super admin|'.$superAdminModule)) {
                return true;
            }
            return false;
        });
    }
}
