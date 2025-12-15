<?php

return [
    // Passport provider (add this line if it’s not there)
    Laravel\Passport\PassportServiceProvider::class,

    // Your app providers
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
];
