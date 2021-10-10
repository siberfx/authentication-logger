<?php

return [
    // The database table name
    // You can change this if the database keys get too long for your driver
    'table_name' => 'auth_logger',

    'notifications' => [
        'new-device' => [
            // Send the NewDevice notification
            'enabled' => env('NEW_DEVICE_NOTIFICATION', false),

            // Use torann/geoip to attempt to get a location
            'location' => false,

            // The Notification class to send
            'template' => \Siberfx\AuthenticationLogger\Notifications\NewDevice::class,
        ],
        'failed-login' => [
            // Send the FailedLogin notification
            'enabled' => env('FAILED_LOGIN_NOTIFICATION', true),

            // Use torann/geoip to attempt to get a location
            'location' => false,

            // The Notification class to send
            'template' => \Siberfx\AuthenticationLogger\Notifications\FailedLogin::class,
        ],
    ],

    // When the clean-up command is run, delete old logs greater than `purge` days
    // Don't schedule the clean-up command if you want to keep logs forever.
    'purge' => 365,
];
