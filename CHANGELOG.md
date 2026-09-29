# Changelog

All notable changes to `Authentication Logger` will be documented in this file.

## 2.0.0 - Unreleased

Requires PHP 8.4 or 8.5 and Laravel 12 or 13.

### Added
- `AuthLogger` is `MassPrunable`, honouring the `purge` config (was documented but never implemented).
- `successful()`, `failed()` and `fromDevice()` query scopes (`#[Scope]` attribute).
- `latestAuthentication()` relation (`latestOfMany`) for N+1-free listings.
- `model` config option to swap the Eloquent model, and `listeners` toggles per auth event.
- `toArray()` on notifications so the `database`/`broadcast` channels work.
- Abstract `AuthenticationNotification` base class for custom templates.
- Useful factory definition with `failed()` / `loggedOut()` states.
- Test suite (Orchestra Testbench) and a PHP 8.4/8.5 × Laravel 12/13 CI matrix.

### Fixed
- Mail views and JSON translations were never registered, so notifications could not render.
- Failed-login mail passed the account name as a string while the view read `->email`.
- `{{ $location['state'], 'Unknown State' }}` Blade typo, and undefined `default` key access.
- `geoip()` fatal error when `torann/geoip` was not installed; lookups are now guarded.
- Failed-login location used the `new-device` location setting.
- Listeners crashed for authenticatable models without the `AuthenticationLoggable` trait.
- "Log out other devices" ran one query per log; it is now a single update and keeps existing logout times.
- README referenced a non-existent service provider and publish tags.

### Changed (breaking)
- Publish tags are now `auth-logger-config`, `auth-logger-migrations`, `auth-logger-views`, `auth-logger-translations`. Migrations are published with a timestamp.
- The notification property `$AuthLogger` was renamed to the read-only `$authLog`.
- `previousLoginAt()` / `previousLoginIp()` only consider successful logins.
- Listeners are `readonly` classes, and trait methods declare return types.
- Mail views use `<x-mail::message>` components with a shared details partial.

## 1.0.0 - 2021-10-10
