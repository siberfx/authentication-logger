<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Siberfx\AuthenticationLogger\AuthenticationLogger;
use Siberfx\AuthenticationLogger\Notifications\FailedLogin;

readonly class FailedLoginListener
{
    public function __construct(private Request $request) {}

    public function handle(Failed $event): void
    {
        $user = $event->user;

        if (! AuthenticationLogger::tracks($user)) {
            return;
        }

        $ip = $this->request->ip();

        $log = $user->authentications()->create([
            'ip_address' => $ip,
            'user_agent' => $this->request->userAgent(),
            'login_at' => now(),
            'login_successful' => false,
            'location' => AuthenticationLogger::location($ip, 'failed-login'),
        ]);

        if (! config('auth-logger.notifications.failed-login.enabled')) {
            return;
        }

        $notification = config('auth-logger.notifications.failed-login.template') ?? FailedLogin::class;

        $user->notify(new $notification($log));
    }
}
