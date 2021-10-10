<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

class LogoutListener
{
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Logout $event): void
    {
        if ($event->user) {
            $user = $event->user;
            $ip = $this->request->ip();
            $userAgent = $this->request->userAgent();
            $log = $user->authentications()->whereIpAddress($ip)->whereUserAgent($userAgent)->first();

            if (! $log) {
                $log = new AuthLogger([
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                ]);
            }

            $log->logout_at = now();

            $user->authentications()->save($log);
        }
    }
}
