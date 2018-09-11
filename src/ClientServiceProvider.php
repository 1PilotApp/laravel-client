<?php

namespace OnePilot\Client;

use Illuminate\Support\ServiceProvider;
use OnePilot\Client\Classes\ComposerPackageDetector;
use OnePilot\Client\Contracts\PackageDetector;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->publishes([
            __DIR__ . '/../config/onepilot.php' => config_path('onepilot.php'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PackageDetector::class, ComposerPackageDetector::class);
    }
}
