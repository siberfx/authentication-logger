<?php

namespace Siberfx\AuthenticationLogger\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

/**
 * Base class for the package notifications. Extend it (or one of its children)
 * and point the `template` config option at your class to customise delivery.
 */
abstract class AuthenticationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AuthLogger $authLog) {}

    /**
     * The mail subject line.
     */
    abstract protected function subject(): string;

    /**
     * The markdown mail view.
     */
    abstract protected function view(): string;

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return method_exists($notifiable, 'notifyAuthenticationLogVia')
            ? $notifiable->notifyAuthenticationLogVia()
            : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return new MailMessage()
            ->subject($this->subject())
            ->markdown($this->view(), [
                'account' => $notifiable,
                'time' => $this->authLog->login_at,
                'ipAddress' => $this->authLog->ip_address,
                'browser' => $this->authLog->user_agent,
                'location' => $this->authLog->location,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->authLog->getKey(),
            'ip_address' => $this->authLog->ip_address,
            'user_agent' => $this->authLog->user_agent,
            'login_at' => $this->authLog->login_at?->toIso8601String(),
            'login_successful' => $this->authLog->login_successful,
            'location' => $this->authLog->location,
        ];
    }
}
