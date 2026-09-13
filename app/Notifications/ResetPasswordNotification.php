<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = sprintf(
            '%s/reset-password?token=%s&email=%s',
            rtrim(config('app.frontend_url'), '/'),
            $this->token,
            urlencode($notifiable->getEmailForPasswordReset()),
        );

        return (new MailMessage)
            ->subject('Recuperação de palavra-passe')
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Recebemos um pedido para repor a palavra-passe da sua conta.')
            ->action('Repor palavra-passe', $url)
            ->line('Este link expira em '.config('auth.passwords.users.expire').' minutos.')
            ->line('Se não solicitou esta alteração, pode ignorar este email.');
    }
}
