<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

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
        // Fortifyのログインビューを設定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // Fortifyのメール認証ビューを設定
        Fortify::verifyEmailView(function () {
            return view('auth.verify');
        });
    }
}
