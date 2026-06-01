<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Exceptions\WorkflowException;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;

class DevolverPedidoAction
{
    public function execute(Utilizador $user, Pedido $pedido, ?string $comentario): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual !== EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::RASCUNHO);
        }

        // $comentario é recebido mas não persistido (decisão de negócio acordada)
        return $this->transicionar($user, $pedido, EstadoPedidoEnum::RASCUNHO);
    }

    private function transicionar(Utilizador $user, Pedido $pedido, EstadoPedidoEnum $destino): Pedido
    {
        $estadoDestino = EstadoPedido::where('nome', $destino->value)->firstOrFail();

        HistoricoPedido::create([
            'id_pedido'          => $pedido->id_pedido,
            'id_utilizador_acao' => $user->id_utilizador,
            'id_estado_anterior' => $pedido->id_estado_pedido,
            'id_estado_novo'     => $estadoDestino->id_estado_pedido,
            'data_alteracao'     => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $estadoDestino->id_estado_pedido]);

        return $pedido->fresh(['estadoPedido', 'tipoPedido', 'utilizador']);
    }
}
