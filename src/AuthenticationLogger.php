<?php

namespace Siberfx\AuthenticationLogger;

use Illuminate\Database\Eloquent\Model;
use Siberfx\AuthenticationLogger\Models\AuthLogger;
use Siberfx\AuthenticationLogger\Traits\AuthenticationLoggable;

/**
 * Small, stateless helpers shared by the listeners and the trait.
 */
final class AuthenticationLogger
{
    /**
     * @return class-string<AuthLogger>
     */
    public static function model(): string
    {
        return config('auth-logger.model') ?? AuthLogger::class;
    }

    /**
     * Whether the given user opted in to authentication logging.
     */
    public static function tracks(mixed $user): bool
    {
        return $user instanceof Model
            && in_array(AuthenticationLoggable::class, class_uses_recursive($user), true);
    }

    /**
     * Resolve the location of an IP address through torann/geoip, when it is installed and enabled.
     *
     * @param  'new-device'|'failed-login'  $notification
     * @return array<string, mixed>|null
     */
    public static function location(?string $ipAddress, string $notification): ?array
    {
        if ($ipAddress === null
            || ! config("auth-logger.notifications.{$notification}.location")
            || ! function_exists('geoip')) {
            return null;
        }

        try {
            return geoip()->getLocation($ipAddress)?->toArray();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
