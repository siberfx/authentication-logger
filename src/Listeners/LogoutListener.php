<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Siberfx\AuthenticationLogger\AuthenticationLogger;

readonly class LogoutListener
{
    public function __construct(private Request $request) {}

    public function handle(Logout $event): void
    {
        $user = $event->user;

        if (! AuthenticationLogger::tracks($user)) {
            return;
        }

        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();

        $log = $user->authentications()->successful()->fromDevice($ip, $userAgent)->first()
            ?? $user->authentications()->make(['ip_address' => $ip, 'user_agent' => $userAgent]);

        $log->logout_at = now();
        $log->save();
    }
}
