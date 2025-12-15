<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 🔐 CPD admin gate
        Gate::define('manage-cpd', function (User $user) {
            return (bool) $user->is_cpd_admin
                || in_array($user->email, [
                    'bkeolopile@idmb.ac.bw',   // your IDM CPD admin email
                ]);
        });
    }
}

