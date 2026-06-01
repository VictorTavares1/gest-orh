<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Exceptions\WorkflowException;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;

class CancelarPedidoAction
{
    public function execute(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        if (! $estadoAtual->podeTransicionarPara(EstadoPedidoEnum::CANCELADO)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::CANCELADO);
        }

        $estadoCancelado = EstadoPedido::where('nome', EstadoPedidoEnum::CANCELADO->value)->firstOrFail();

        HistoricoPedido::create([
            'id_pedido'          => $pedido->id_pedido,
            'id_utilizador_acao' => $user->id_utilizador,
            'id_estado_anterior' => $pedido->id_estado_pedido,
            'id_estado_novo'     => $estadoCancelado->id_estado_pedido,
            'data_alteracao'     => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $estadoCancelado->id_estado_pedido]);

        return $pedido->fresh(['estadoPedido', 'tipoPedido', 'utilizador']);
    }
}
