<?php

namespace App\Providers;

use Carbon\Carbon;
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

        Passport::tokensExpireIn(Carbon::now()->addHours(3));


        Passport::refreshTokensExpireIn(Carbon::now()->addHours(4));



        Passport::enablePasswordGrant();
    }
}
