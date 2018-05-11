<?php

namespace CmsPilot\LaravelClient;

use Illuminate\Support\ServiceProvider;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->publishes([
            __DIR__.'/config/cmspilot.php' => config_path('cmspilot.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}