<?php

namespace Siberfx\AuthenticationLogger;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Siberfx\AuthenticationLogger\Listeners\FailedLoginListener;
use Siberfx\AuthenticationLogger\Listeners\LoginListener;
use Siberfx\AuthenticationLogger\Listeners\LogoutListener;
use Siberfx\AuthenticationLogger\Listeners\OtherDeviceLogoutListener;

class AuthenticationLoggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        // publish config file
        $this->publishes([__DIR__.'/../config' => config_path()], 'config');

        // publish migration file
        $this->publishes([__DIR__.'/../database/migrations' => database_path('migrations')], 'migrations');

        // register the authentication event listeners
        Event::listen(Login::class, LoginListener::class);
        Event::listen(Failed::class, FailedLoginListener::class);
        Event::listen(Logout::class, LogoutListener::class);
        Event::listen(OtherDeviceLogout::class, OtherDeviceLogoutListener::class);
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-logger.php', 'auth-logger');
    }
}
