<?php

namespace Siberfx\AuthenticationLogger\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Siberfx\AuthenticationLogger\AuthenticationLogger;
use Siberfx\AuthenticationLogger\Notifications\NewDevice;

readonly class LoginListener
{
    public function __construct(private Request $request) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! AuthenticationLogger::tracks($user)) {
            return;
        }

        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();

        $known = $user->authentications()->successful()->fromDevice($ip, $userAgent)->exists();

        $log = $user->authentications()->create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_at' => now(),
            'login_successful' => true,
            'location' => AuthenticationLogger::location($ip, 'new-device'),
        ]);

        if ($known || $this->isNewlyRegistered($user) || ! config('auth-logger.notifications.new-device.enabled')) {
            return;
        }

        $notification = config('auth-logger.notifications.new-device.template') ?? NewDevice::class;

        $user->notify(new $notification($log));
    }

    /**
     * A user who registered within the last minute is logging in for the first time,
     * so there is no "other" device to warn them about.
     */
    private function isNewlyRegistered(Model $user): bool
    {
        $createdAt = $user->usesTimestamps() ? $user->getAttribute($user->getCreatedAtColumn()) : null;

        return $createdAt !== null && Date::parse($createdAt)->isAfter(now()->subMinute());
    }
}
