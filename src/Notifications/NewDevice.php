<?php

namespace Siberfx\AuthenticationLogger\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

class NewDevice extends Notification implements ShouldQueue
{
    use Queueable;

    public AuthLogger $AuthLogger;

    public function __construct(AuthLogger $AuthLogger)
    {
        $this->AuthLogger = $AuthLogger;
    }

    public function via($notifiable)
    {
        return $notifiable->notifyAuthLoggerVia();
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__('Your :app account logged in from a new device.', ['app' => config('app.name')]))
            ->markdown('auth-logger::emails.new', [
                'account' => $notifiable,
                'time' => $this->AuthLogger->login_at,
                'ipAddress' => $this->AuthLogger->ip_address,
                'browser' => $this->AuthLogger->user_agent,
                'location' => $this->AuthLogger->location,
            ]);
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage())
            ->from(config('app.name'))
            ->warning()
            ->content(__('Your :app account logged in from a new device.', ['app' => config('app.name')]))
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->fields([
                    __('Account') => $notifiable->email,
                    __('Time') => $this->AuthLogger->login_at->toCookieString(),
                    __('IP Address') => $this->AuthLogger->ip_address,
                    __('Browser') => $this->AuthLogger->user_agent,
                    __('Location') =>
                        $this->AuthLogger->location &&
                        $this->AuthLogger->location['default'] === false ?
                            ($this->AuthLogger->location['city'] ?? 'N/A') . ', ' . ($this->AuthLogger->location['state'] ?? 'N/A') :
                            'Unknown',
                ]);
            });
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage())
            ->content(__('Your :app account logged in from a new device.', ['app' => config('app.name')]));
    }
}
