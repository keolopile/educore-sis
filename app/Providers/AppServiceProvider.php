<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Tell Passport which Blade view to use for the authorization screen.
         * We'll create this view next.
         */
        Passport::authorizationView('auth.oauth.authorize');
    }
}
