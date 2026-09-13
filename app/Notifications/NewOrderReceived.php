<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public readonly Order $order) {}

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
        $order = $this->order->loadMissing('customer', 'items');

        $message = (new MailMessage)
            ->subject('Nova encomenda recebida — '.$order->number)
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line('Acabou de receber uma nova encomenda na sua loja.')
            ->line('Número da encomenda: '.$order->number)
            ->line('Cliente: '.$order->customer?->name)
            ->line('Número de artigos: '.$order->items->count())
            ->line('Total: '.number_format((float) $order->total, 2, ',', '.'));

        return $message->action('Ver encomenda', config('app.frontend_url').'/orders/'.$order->id);
    }
}
