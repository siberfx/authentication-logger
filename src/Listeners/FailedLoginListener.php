<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Siberfx\AuthenticationLogger\Notifications\FailedLogin;

class FailedLoginListener
{
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Failed $event): void
    {
        if ($event->user) {
            $log = $event->user->authentications()->create([
                'ip_address' => $ip = $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
                'login_at' => now(),
                'login_successful' => false,
                'location' => config('auth-logger.notifications.new-device.location') ? optional(geoip()->getLocation($ip))->toArray() : null,
            ]);

            if (config('auth-logger.notifications.failed-login.enabled')) {
                $failedLogin = config('auth-logger.notifications.failed-login.template') ?? FailedLogin::class;
                $event->user->notify(new $failedLogin($log));
            }
        }
    }
}
