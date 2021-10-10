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
        $configPath = __DIR__.'/../config/auth-logger.php';
        $this->publishes([$configPath => config_path('auth-logger.php')]);

        $this->publishes([__DIR__.'/database/migrations' => database_path('migrations')], 'migrations');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/auth-logger.php';
        $this->mergeConfigFrom($configPath, 'auth-logger');

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('authentication-logger');
    }

}
