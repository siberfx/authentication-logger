# Authentication Logger for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/siberfx/authentication-logger.svg?style=flat-square)](https://packagist.org/packages/siberfx/authentication-logger)
[![Total Downloads](https://img.shields.io/packagist/dt/siberfx/authentication-logger.svg?style=flat-square)](https://packagist.org/packages/siberfx/authentication-logger)
[![Tests](https://img.shields.io/github/actions/workflow/status/siberfx/authentication-logger/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/siberfx/authentication-logger/actions/workflows/run-tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4%20%7C%208.5-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/releases/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x%20%7C%2013.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/siberfx/authentication-logger.svg?style=flat-square)](LICENSE.md)

Track every authentication on your Laravel application — **successful logins, failed attempts, logouts and "log out other devices"** — together with the IP address, user agent and (optionally) the geographic location. Users are notified when their account is accessed from a **new device** or when a **login attempt fails**.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
- [How It Works](#how-it-works)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Login history helpers](#login-history-helpers)
  - [Query scopes](#query-scopes)
  - [Displaying the log](#displaying-the-log)
- [Notifications](#notifications)
  - [Channels](#channels)
  - [Custom notifications](#custom-notifications)
  - [Customising the emails](#customising-the-emails)
- [Location Tracking](#location-tracking)
- [Pruning Old Logs](#pruning-old-logs)
- [Database Schema](#database-schema)
- [Testing](#testing)
- [Upgrading from 1.x](#upgrading-from-1x)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

---

## Features

- 📝 Logs **logins, failed logins, logouts** and **other-device logouts** automatically.
- 🔔 **New device** and **failed login** notifications (queued, via any notification channel).
- 🌍 Optional **location** lookup via [`torann/geoip`](https://github.com/Torann/laravel-geoip).
- 🧹 Built-in **pruning** through Laravel's `model:prune`.
- 🔍 Handy history helpers: `lastLoginAt()`, `previousLoginIp()`, `latestAuthentication`…
- 🧩 Fully configurable: table name, model, notification classes, and each listener can be toggled.
- ✅ Works with **any authenticatable model** and any guard; models without the trait are ignored.
- 🚀 Modern codebase: PHP 8.4+ syntax, Laravel 12+ attributes, full test suite.

## Requirements

| Package version | PHP        | Laravel    |
| --------------- | ---------- | ---------- |
| **2.x**         | 8.4, 8.5   | 12.x, 13.x |

## Installation

Install the package via Composer:

```bash
composer require siberfx/authentication-logger
```

The service provider is registered automatically through package auto-discovery.

Publish and run the migration:

```bash
php artisan vendor:publish --tag="auth-logger-migrations"
php artisan migrate
```

Optionally publish the config file, mail views and translations:

```bash
php artisan vendor:publish --tag="auth-logger-config"
php artisan vendor:publish --tag="auth-logger-views"
php artisan vendor:publish --tag="auth-logger-translations"
```

| Tag                        | Publishes to                          |
| -------------------------- | ------------------------------------- |
| `auth-logger-config`       | `config/auth-logger.php`              |
| `auth-logger-migrations`   | `database/migrations/` (timestamped)  |
| `auth-logger-views`        | `resources/views/vendor/auth-logger/` |
| `auth-logger-translations` | `lang/vendor/auth-logger/`            |

## Setup

Add the `AuthenticationLoggable` and `Notifiable` traits to every authenticatable model you want to track:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Siberfx\AuthenticationLogger\Traits\AuthenticationLoggable;

class User extends Authenticatable
{
    use AuthenticationLoggable;
    use Notifiable;
}
```

That's it — no event registration is required.

## How It Works

The package listens for Laravel's built-in authentication events:

| Event                                   | Listener                    | What is recorded                                                                 |
| --------------------------------------- | --------------------------- | -------------------------------------------------------------------------------- |
| `Illuminate\Auth\Events\Login`          | `LoginListener`             | A successful login. Sends the **new device** notification for unknown devices.   |
| `Illuminate\Auth\Events\Failed`         | `FailedLoginListener`       | A failed attempt against an existing account. Sends the **failed login** notification. |
| `Illuminate\Auth\Events\Logout`         | `LogoutListener`            | Sets `logout_at` on the log of the current device.                               |
| `Illuminate\Auth\Events\OtherDeviceLogout` | `OtherDeviceLogoutListener` | Marks all other open sessions as `cleared_by_user` and sets their `logout_at`. |

A **device** is identified by the combination of IP address and user agent. `Auth::logoutOtherDevices($password)` fires the `OtherDeviceLogout` event.

Each listener can be disabled in the `listeners` config section.

## Configuration

This is the content of the published `config/auth-logger.php`:

```php
return [
    // Table that stores the logs
    'table_name' => env('AUTH_LOGGER_TABLE', 'auth_logger'),

    // Swap for your own subclass of AuthLogger if needed
    'model' => \Siberfx\AuthenticationLogger\Models\AuthLogger::class,

    // Toggle which auth events are recorded
    'listeners' => [
        'login' => true,
        'failed' => true,
        'logout' => true,
        'other-device-logout' => true,
    ],

    'notifications' => [
        'new-device' => [
            'enabled' => env('NEW_DEVICE_NOTIFICATION', false),
            'location' => env('NEW_DEVICE_NOTIFICATION_LOCATION', false),
            'template' => \Siberfx\AuthenticationLogger\Notifications\NewDevice::class,
        ],
        'failed-login' => [
            'enabled' => env('FAILED_LOGIN_NOTIFICATION', true),
            'location' => env('FAILED_LOGIN_NOTIFICATION_LOCATION', false),
            'template' => \Siberfx\AuthenticationLogger\Notifications\FailedLogin::class,
        ],
    ],

    // Days to keep logs; null keeps them forever
    'purge' => env('AUTH_LOGGER_PURGE_DAYS', 60),
];
```

### Environment variables

| Variable                              | Default       | Description                                       |
| ------------------------------------- | ------------- | ------------------------------------------------- |
| `AUTH_LOGGER_TABLE`                   | `auth_logger` | Table name for the logs.                          |
| `NEW_DEVICE_NOTIFICATION`             | `false`       | Send the new device notification.                 |
| `NEW_DEVICE_NOTIFICATION_LOCATION`    | `false`       | Resolve the location for new device logins.       |
| `FAILED_LOGIN_NOTIFICATION`           | `true`        | Send the failed login notification.               |
| `FAILED_LOGIN_NOTIFICATION_LOCATION`  | `false`       | Resolve the location for failed logins.           |
| `AUTH_LOGGER_PURGE_DAYS`              | `60`          | Age in days after which logs are pruned.          |

## Usage

### Login history helpers

The `AuthenticationLoggable` trait adds the following to your model:

```php
$user->authentications;          // Collection of all logs, newest first
$user->latestAuthentication;     // The most recent log (eager-loadable)

$user->lastLoginAt();            // ?Carbon  — including failed attempts
$user->lastSuccessfulLoginAt();  // ?Carbon
$user->lastLoginIp();            // ?string  — including failed attempts
$user->lastSuccessfulLoginIp();  // ?string

$user->previousLoginAt();        // ?Carbon  — the successful login before the current one
$user->previousLoginIp();        // ?string
```

A common use is showing a "last seen" hint after login:

```blade
@if ($at = auth()->user()->previousLoginAt())
    Your last login was {{ $at->diffForHumans() }} from {{ auth()->user()->previousLoginIp() }}.
@endif
```

### Query scopes

The `AuthLogger` model ships with query scopes:

```php
use Siberfx\AuthenticationLogger\Models\AuthLogger;

AuthLogger::successful()->count();

AuthLogger::failed()
    ->where('login_at', '>=', now()->subDay())
    ->get();

$user->authentications()->fromDevice($ip, $userAgent)->exists();
```

Eager load the latest log to avoid N+1 queries in admin listings:

```php
User::with('latestAuthentication')->paginate();
```

### Displaying the log

An example Blade table of the current user's sessions:

```blade
<table>
    <thead>
        <tr>
            <th>IP Address</th>
            <th>Browser</th>
            <th>Location</th>
            <th>Login At</th>
            <th>Successful</th>
            <th>Logout At</th>
        </tr>
    </thead>
    <tbody>
        @foreach (auth()->user()->authentications()->limit(20)->get() as $log)
            <tr>
                <td>{{ $log->ip_address }}</td>
                <td>{{ $log->user_agent }}</td>
                <td>{{ $log->location ? ($log->location['city'] ?? '') . ', ' . ($log->location['state'] ?? '') : '—' }}</td>
                <td>{{ $log->login_at?->toDayDateTimeString() }}</td>
                <td>{{ $log->login_successful ? 'Yes' : 'No' }}</td>
                <td>{{ $log->logout_at?->toDayDateTimeString() ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

> **Tip:** for friendlier browser names, parse `user_agent` with a library such as `jenssegers/agent` or `whichbrowser/parser`.

## Notifications

| Notification  | Sent when                                                                                                   | Default   |
| ------------- | ----------------------------------------------------------------------------------------------------------- | --------- |
| `NewDevice`   | A user logs in from an IP / user agent that has never logged in successfully before (skipped for users who registered in the last minute). | Disabled |
| `FailedLogin` | A login attempt against an existing account fails.                                                          | Enabled   |

Both notifications implement `ShouldQueue`, so run a queue worker in production.

### Channels

Notifications are delivered via `mail` by default. Define `notifyAuthenticationLogVia()` on your model to choose other channels:

```php
public function notifyAuthenticationLogVia(): array
{
    return ['mail', 'database'];
}
```

Both notifications implement `toArray()`, so the `database` and `broadcast` channels work out of the box. For Slack, SMS or other channels, install the matching [notification channel](https://laravel-notification-channels.com) and add a `toSlack()` / `toVonage()` method in a custom notification.

### Custom notifications

Extend a built-in notification (or the abstract `AuthenticationNotification`) and register it as the `template` in the config:

```php
use Illuminate\Notifications\Messages\MailMessage;
use Siberfx\AuthenticationLogger\Notifications\NewDevice;

class MyNewDevice extends NewDevice
{
    public function toMail(object $notifiable): MailMessage
    {
        return parent::toMail($notifiable)
            ->action('Review account activity', route('security.index'));
    }
}
```

```php
// config/auth-logger.php
'template' => \App\Notifications\MyNewDevice::class,
```

The logged `AuthLogger` record is available as `$this->authLog`.

### Customising the emails

Publish the views with `--tag="auth-logger-views"` and edit:

- `resources/views/vendor/auth-logger/emails/new.blade.php`
- `resources/views/vendor/auth-logger/emails/failed.blade.php`
- `resources/views/vendor/auth-logger/emails/partials/details.blade.php`

The views receive `$account`, `$time`, `$ipAddress`, `$browser` and `$location`. All strings are translatable through the JSON translation file.

## Location Tracking

To record where a login came from, install [`torann/geoip`](https://github.com/Torann/laravel-geoip) and publish its config:

```bash
composer require torann/geoip
php artisan vendor:publish --provider="Torann\GeoIP\GeoIPServiceProvider" --tag=config
```

Then enable location per notification type:

```dotenv
NEW_DEVICE_NOTIFICATION_LOCATION=true
FAILED_LOGIN_NOTIFICATION_LOCATION=true
```

The location is stored in the `location` JSON column. When `torann/geoip` is not installed, or the lookup fails, the location is skipped silently (errors are reported, never thrown).

> **Note:** when working locally, `geoip` returns its configured *default* location. The mail templates hide default locations.

## Pruning Old Logs

`AuthLogger` uses Laravel's `MassPrunable`. Logs older than the `purge` number of days are removed by the `model:prune` command. Because `model:prune` only discovers models inside `app/Models`, pass the package model explicitly — e.g. in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

Schedule::command('model:prune', ['--model' => [AuthLogger::class]])->daily();
```

Set `purge` to `null` to keep logs forever.

## Database Schema

| Column                 | Type          | Description                                    |
| ---------------------- | ------------- | ---------------------------------------------- |
| `id`                   | bigint        | Primary key.                                   |
| `authenticatable_type` | string        | Morph type of the user model.                  |
| `authenticatable_id`   | bigint        | Morph id of the user.                          |
| `ip_address`           | string(45)    | IPv4 / IPv6 address.                           |
| `user_agent`           | text          | Browser user agent.                            |
| `login_at`             | timestamp     | When the login attempt happened (indexed).     |
| `login_successful`     | boolean       | Whether the attempt succeeded.                 |
| `logout_at`            | timestamp     | When the session ended.                        |
| `cleared_by_user`      | boolean       | Ended through "log out other devices".         |
| `location`             | json          | Resolved geo location, if enabled.             |

## Testing

```bash
composer test
```

The suite runs on [Orchestra Testbench](https://github.com/orchestral/testbench) with an in-memory SQLite database and is executed in CI against **PHP 8.4 / 8.5** and **Laravel 12 / 13**.

## Upgrading from 1.x

1. Require PHP 8.4+ and Laravel 12+.
2. Update publish tags: `authentication-log-*` → `auth-logger-*`.
3. In custom notifications, rename `$this->AuthLogger` to `$this->authLog`.
4. `previousLoginAt()` / `previousLoginIp()` now only consider **successful** logins.
5. Re-publish the views if you customised them — they now use `<x-mail::message>` and a shared `partials/details` view.
6. If you relied on pruning, schedule `model:prune` with the `--model` option as shown above.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Selim Gormus](https://github.com/siberfx)
- [All Contributors](https://github.com/siberfx/authentication-logger/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
