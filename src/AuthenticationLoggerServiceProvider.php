<?php

namespace Siberfx\AuthenticationLogger;

use Illuminate\Support\ServiceProvider;

class AuthenticationLoggerServiceProvider extends ServiceProvider {

    // create_authentication_log_table

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

        // publish config file
        $this->publishes([__DIR__.'/../config' => config_path()], 'config');

        // publish migration file
        $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'migrations');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }


}
