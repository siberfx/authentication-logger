<?php

namespace Siberfx\AuthenticationLogger;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Siberfx\AuthenticationLogger\Commands\PurgeAuthenticationLogCommand;
use Siberfx\AuthenticationLogger\Listeners\FailedLoginListener;
use Siberfx\AuthenticationLogger\Listeners\LoginListener;
use Siberfx\AuthenticationLogger\Listeners\LogoutListener;
use Siberfx\AuthenticationLogger\Listeners\OtherDeviceLogoutListener;

use Illuminate\Contracts\Events\Dispatcher;


class AuthenticationLoggerServiceProvider extends ServiceProvider
{

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
//        $events = $this->app->make(Dispatcher::class);
//        $events->listen(Login::class, LoginListener::class);
//        $events->listen(Failed::class, FailedLoginListener::class);
//        $events->listen(Logout::class, LogoutListener::class);
//        $events->listen(OtherDeviceLogout::class, OtherDeviceLogoutListener::class);
    }


}
