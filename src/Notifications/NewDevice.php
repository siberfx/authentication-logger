<?php

namespace Siberfx\AuthenticationLogger\Notifications;

class NewDevice extends AuthenticationNotification
{
    protected function subject(): string
    {
        return __('Your :app account logged in from a new device.', ['app' => config('app.name')]);
    }

    protected function view(): string
    {
        return 'auth-logger::emails.new';
    }
}
