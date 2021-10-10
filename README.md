
Authentication Logger is a package which tracks your user's authentication information such as login/logout time, IP, Browser, Location, etc. as well as sends out notifications via mail, slack, or sms for new devices and failed logins.

## Installation

You can install the package via composer:

```bash
composer require siberfx/laravel-authentication-log
```

If you want the location features you must also install `torann/geoip`:

```bash
composer require torann/geoip
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Siberfx\AuthenticationLogger\AuthenticationLogServiceProviderger" --tag="authentication-log-migrations"
php artisan migrate
```

You can publish the view/email files with:
```bash
php artisan vendor:publish --provider="Siberfx\AuthenticationLogger\AuthenticationLogServiceProviderger" --tag="authentication-log-views"
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Siberfx\AuthenticationLogger\AuthenticationLogServiceProviderger" --tag="authentication-log-config"
```

This is the contents of the published config file:

```php
return [
    // The database table name
    // You can change this if the database keys get too long for your driver
    'table_name' => 'authentication_log',

    'notifications' => [
        'new-device' => [
            // Send the NewDevice notification
            'enabled' => env('NEW_DEVICE_NOTIFICATION', true),

            // Use torann/geoip to attempt to get a location
            'location' => true,

            // The Notification class to send
            'template' => \Siberfx\AuthenticationLogger\Notifications\NewDevice::class,
        ],
        'failed-login' => [
            // Send the FailedLogin notification
            'enabled' => env('FAILED_LOGIN_NOTIFICATION', false),

            // Use torann/geoip to attempt to get a location
            'location' => true,

            // The Notification class to send
            'template' => \Siberfx\AuthenticationLogger\Notifications\FailedLogin::class,
        ],
    ],

    // When the clean-up command is run, delete old logs greater than `purge` days
    // Don't schedule the clean-up command if you want to keep logs forever.
    'purge' => 365,
];
```

If you installed `torann/geoip` you should also publish that config file to set your defaults:

```
php artisan vendor:publish --provider="Torann\GeoIP\GeoIPServiceProvider" --tag=config
```

## Configuration

You must add the `AuthenticationLoggable` and `Notifiable` traits to the models you want to track.

```php
use Illuminate\Notifications\Notifiable;
use Siberfx\AuthenticationLogger\Traits\AuthenticationLoggable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, AuthenticationLoggable;
}
```

The package will listen for Laravel's Login, Logout, Failed, and OtherDeviceLogout events.

## Usage

Get all authentication logs for the user:
```php
User::find(1)->authentications;
```

Get the user's last login information:
```php
User::find(1)->lastLoginAt();

User::find(1)->lastSuccessfulLoginAt();

User::find(1)->lastLoginIp();

User::find(1)->lastSuccessfulLoginIp();
```

Get the user's previous login time & IP address (ignoring the current login):
```php
auth()->user()->previousLoginAt();

auth()->user()->previousLoginIp();
```

### Notifications

Notifications may be sent on the `mail`, `nexmo`, and `slack` channels but by **default notify via email**.

You may define a `notifyAuthenticationLogVia` method  on your authenticatable models to determine which channels the notification should be delivered on:

```php
public function notifyAuthenticationLogVia()
{
    return ['nexmo', 'mail', 'slack'];
}
```

You must install the [Slack](https://laravel.com/docs/8.x/notifications#routing-slack-notifications) and [Nexmo](https://laravel.com/docs/8.x/notifications#routing-sms-notifications) drivers to use those routes and follow their documentation on setting it up for your specific authenticatable models.

#### New Device Notifications

Enabled by default, they use the `\Siberfx\AuthenticationLogger\Notifications\NewDevice` class which can be overridden in the config file.

#### Failed Login Notifications

Disabled by default, they use the `\Siberfx\AuthenticationLogger\Notifications\FailedLogin` class which can be overridden in the config file.

#### Location

If the `torann/geoip` package is installed, it will attempt to include location information to the notifications by default.

You can turn this off within the configuration for each template.

**Note:** By default when working locally, no location will be recorded because it will send back the `default address` from the `geoip` config file. You can override this behavior in the email templates.

## Purging Old Logs

You may clear the old authentication log records using the `authentication-log:purge` Artisan command:

```
php artisan authentication-log:purge
```

Records that are older than the number of days specified in the `purge` option in your `config/authentication-log.php` will be deleted.

```php
'purge' => 365,
```

You can also schedule the command at an interval:

```php
$schedule->command('authentication-log:purge')->monthly();
```

## Displaying The Log

**Note:** This example uses the `jenssegers/agent` package which is included by default with Laravel Jetstream as well as `jamesmills/laravel-timezone` for displaying timezones in the users local timezone. Both are optional, modify the table to fit your needs.

