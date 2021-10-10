<?php

namespace Siberfx\AuthenticationLogger;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Contracts\Events\Dispatcher;
use Siberfx\AuthenticationLogger\Commands\PurgeAuthenticationLogCommand;
use Siberfx\AuthenticationLogger\Listeners\FailedLoginListener;
use Siberfx\AuthenticationLogger\Listeners\LoginListener;
use Siberfx\AuthenticationLogger\Listeners\LogoutListener;
use Siberfx\AuthenticationLogger\Listeners\OtherDeviceLogoutListener;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuthenticationLoggerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('auth-logger')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_authentication_log_table')
            ->hasCommand(PurgeAuthenticationLogCommand::class);

        $events = $this->app->make(Dispatcher::class);
        $events->listen(Login::class, LoginListener::class);
        $events->listen(Failed::class, FailedLoginListener::class);
        $events->listen(Logout::class, LogoutListener::class);
        $events->listen(OtherDeviceLogout::class, OtherDeviceLogoutListener::class);
    }
}
