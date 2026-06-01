<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\PapelAprovadorEnum;
use App\Exceptions\WorkflowException;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;

class RejeitarPedidoAction
{
    public function execute(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        if (! $estadoAtual->podeTransicionarPara(EstadoPedidoEnum::REJEITADO)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::REJEITADO);
        }

        $papel = $this->determinaPapel($user);

        if ($papel === PapelAprovadorEnum::COLEGA && $estadoAtual !== EstadoPedidoEnum::EM_APROVACAO_COLEGA) {
            throw new WorkflowException('Não tem autoridade para rejeitar este pedido no estado atual.');
        }

        return $this->transicionar($user, $pedido, EstadoPedidoEnum::REJEITADO);
    }

    private function determinaPapel(Utilizador $user): PapelAprovadorEnum
    {
        if ($user->can('aprovacoes.diretora_executiva')) {
            return PapelAprovadorEnum::DIRETORA_EXECUTIVA;
        }

        if ($user->can('aprovacoes.colega')) {
            return PapelAprovadorEnum::COLEGA;
        }

        throw new WorkflowException('Não tem autoridade para aprovar ou rejeitar pedidos.');
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
