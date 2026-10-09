<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignInLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $url) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your sign-in link')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Use the button below to securely sign in to your Food Vending System account.')
            ->action('Sign in', $this->url)
            ->line('This link expires in 15 minutes and can only be used once.')
            ->line('If you did not request this link, you can safely ignore this email.');
    }
}
