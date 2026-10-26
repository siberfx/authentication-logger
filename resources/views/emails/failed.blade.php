<x-mail::message>
# {{ __('Hello') }}!

{{ __('There has been a failed login attempt to your :app account.', ['app' => config('app.name')]) }}

@include('auth-logger::emails.partials.details')

{{ __('If this was you, you can ignore this alert. If you suspect any suspicious activity on your account, please change your password.') }}

{{ __('Regards') }},<br>
{{ config('app.name') }}
</x-mail::message>
