<?php

namespace Siberfx\AuthenticationLogger\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

class FailedLogin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AuthLogger $AuthLogger)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notifyAuthenticationLogVia();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('A failed login to your account'))
            ->markdown('auth-logger::emails.failed', [
                'account' => $notifiable->name,
                'time' => $this->AuthLogger->login_at,
                'ipAddress' => $this->AuthLogger->ip_address,
                'browser' => $this->AuthLogger->user_agent,
                'location' => $this->AuthLogger->location,
            ]);
    }
}
