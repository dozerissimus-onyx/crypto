<?php

namespace App\Providers;

use App\Service\Elliptic;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->bind(Elliptic::class, function ($app) {
//            return new Elliptic($app->make(Client::class));
//        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
