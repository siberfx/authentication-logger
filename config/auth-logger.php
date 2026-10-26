<?php

use Siberfx\AuthenticationLogger\Models\AuthLogger;
use Siberfx\AuthenticationLogger\Notifications\FailedLogin;
use Siberfx\AuthenticationLogger\Notifications\NewDevice;

return [

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | The table that stores authentication logs, and the Eloquent model used
    | to read and write it. Swap the model for your own subclass of
    | AuthLogger if you need extra behaviour.
    |
    */

    'table_name' => env('AUTH_LOGGER_TABLE', 'auth_logger'),

    'model' => AuthLogger::class,

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    |
    | Toggle which of Laravel's authentication events are recorded.
    |
    */

    'listeners' => [
        'login' => true,
        'failed' => true,
        'logout' => true,
        'other-device-logout' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | "location" requires the torann/geoip package. When it is not installed,
    | the location is silently skipped.
    |
    */

    'notifications' => [
        'new-device' => [
            'enabled' => env('NEW_DEVICE_NOTIFICATION', false),
            'location' => env('NEW_DEVICE_NOTIFICATION_LOCATION', false),
            'template' => NewDevice::class,
        ],

        'failed-login' => [
            'enabled' => env('FAILED_LOGIN_NOTIFICATION', true),
            'location' => env('FAILED_LOGIN_NOTIFICATION_LOCATION', false),
            'template' => FailedLogin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Logs older than this many days are removed by `php artisan model:prune`.
    | Set to null to keep logs forever.
    |
    */

    'purge' => env('AUTH_LOGGER_PURGE_DAYS', 60),

];
