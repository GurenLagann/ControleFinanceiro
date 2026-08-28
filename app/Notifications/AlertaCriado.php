<?php

namespace App\Notifications;

use App\Models\Alerta;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertaCriado extends Notification
{
    public function __construct(protected Alerta $alerta)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('[Financeiro] ' . $this->alerta->titulo)
            ->line($this->alerta->mensagem)
            ->line('Tipo: ' . $this->alerta->tipo)
            ->action('Ver alertas', route('alertas.index'));
    }
}
