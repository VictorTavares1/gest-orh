<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PedidoSubmetidoNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $funcionario = $this->pedido->utilizador?->nome ?? 'Um funcionário';
        $tipo        = $this->pedido->tipoPedido?->nome ?? 'pedido';
        $id          = $this->pedido->id_pedido;

        return (new MailMessage)
            ->subject("Novo pedido aguarda aprovação — #{$id}")
            ->greeting("Olá, {$notifiable->nome}!")
            ->line("{$funcionario} submeteu um novo pedido que aguarda a sua aprovação.")
            ->line("**Tipo:** {$tipo}")
            ->line("**Nº do Pedido:** #{$id}")
            ->action('Ver Pedido', url("/pedidos/{$id}"))
            ->line('Gestão RH · Centro Paroquial');
    }
}
