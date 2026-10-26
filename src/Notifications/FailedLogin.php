<?php

namespace Siberfx\AuthenticationLogger\Notifications;

class FailedLogin extends AuthenticationNotification
{
    protected function subject(): string
    {
        return __('A failed login to your account');
    }

    protected function view(): string
    {
        return 'auth-logger::emails.failed';
    }
}
