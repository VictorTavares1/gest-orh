<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PedidoAtualizadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Pedido $pedido,
        private readonly string $acao,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tipo = $this->pedido->tipoPedido?->nome ?? 'Pedido';
        $id   = $this->pedido->id_pedido;

        [$assunto, $linha] = match ($this->acao) {
            'aprovado'  => [
                "Pedido #{$id} aprovado",
                "O seu pedido de **{$tipo}** (#{$id}) foi **aprovado**.",
            ],
            'rejeitado' => [
                "Pedido #{$id} rejeitado",
                "O seu pedido de **{$tipo}** (#{$id}) foi **rejeitado**.",
            ],
            'devolvido' => [
                "Pedido #{$id} devolvido para revisão",
                "O seu pedido de **{$tipo}** (#{$id}) foi **devolvido** para revisão. Por favor, corrija e volte a submeter.",
            ],
            default => [
                "Pedido #{$id} atualizado",
                "O estado do seu pedido de **{$tipo}** (#{$id}) foi atualizado.",
            ],
        };

        return (new MailMessage)
            ->subject($assunto)
            ->greeting("Olá, {$notifiable->nome}!")
            ->line($linha)
            ->action('Ver Pedido', url("/pedidos/{$id}"))
            ->line('Gestão RH · Centro Paroquial');
    }
}
