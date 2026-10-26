<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Http\Request;
use Siberfx\AuthenticationLogger\AuthenticationLogger;

readonly class OtherDeviceLogoutListener
{
    public function __construct(private Request $request) {}

    public function handle(OtherDeviceLogout $event): void
    {
        $user = $event->user;

        if (! AuthenticationLogger::tracks($user)) {
            return;
        }

        $current = $user->authentications()
            ->successful()
            ->fromDevice($this->request->ip(), $this->request->userAgent())
            ->first();

        $user->authentications()
            ->successful()
            ->whereNull('logout_at')
            ->when($current, fn ($query) => $query->whereKeyNot($current->getKey()))
            ->reorder()
            ->update([
                'cleared_by_user' => true,
                'logout_at' => now(),
            ]);
    }
}
