> **{{ __('Account') }}:** {{ $account->email ?? $account->name ?? '' }}<br>
> **{{ __('Time') }}:** {{ $time?->toCookieString() }}<br>
> **{{ __('IP Address') }}:** {{ $ipAddress }}<br>
> **{{ __('Browser') }}:** {{ $browser }}<br>
@if (! empty($location) && ($location['default'] ?? false) === false)
> **{{ __('Location') }}:** {{ $location['city'] ?? __('Unknown City') }}, {{ $location['state'] ?? __('Unknown State') }}
@endif
