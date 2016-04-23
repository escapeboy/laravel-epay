<?php

namespace Escapeboy\Epay;

use Illuminate\Support\ServiceProvider;

class EpayServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
           __DIR__.'/config/epay.php' => config_path('epay.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('epay', function ()
        {
            return new Epay();
        });
    }

    public function provides()
    {
        return ['epay'];
    }
}