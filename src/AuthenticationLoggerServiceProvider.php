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
     * Auth events mapped to their listener, keyed by the `listeners` config toggle.
     */
    private const array LISTENERS = [
        'login' => [Login::class, LoginListener::class],
        'failed' => [Failed::class, FailedLoginListener::class],
        'logout' => [Logout::class, LogoutListener::class],
        'other-device-logout' => [OtherDeviceLogout::class, OtherDeviceLogoutListener::class],
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/auth-logger.php', 'auth-logger');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'auth-logger');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->registerListeners();
    }

    private function registerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/auth-logger.php' => config_path('auth-logger.php'),
        ], 'auth-logger-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'auth-logger-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/auth-logger'),
        ], 'auth-logger-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/auth-logger'),
        ], 'auth-logger-translations');
    }

    private function registerListeners(): void
    {
        foreach (self::LISTENERS as $key => [$event, $listener]) {
            if (config("auth-logger.listeners.{$key}", true)) {
                Event::listen($event, $listener);
            }
        }
    }
}
