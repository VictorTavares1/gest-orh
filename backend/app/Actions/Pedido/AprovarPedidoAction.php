<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\PapelAprovadorEnum;
use App\Exceptions\WorkflowException;
use App\Models\AprovacaoPedido;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;
use App\Notifications\PedidoAtualizadoNotification;

class AprovarPedidoAction
{
    public function execute(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        $papel = $this->determinaPapel($user);

        if ($papel === PapelAprovadorEnum::COLEGA) {
            $pedido->loadMissing('trocaHorario');
            if (! $pedido->trocaHorario || $pedido->trocaHorario->id_colega !== $user->id_utilizador) {
                throw new WorkflowException('Só o colega nomeado no pedido pode aprovar esta Troca de Horário.');
            }
        }

        $proximoEstado = $this->proximoEstado($estadoAtual, $papel);
        $estadoDestino = EstadoPedido::where('nome', $proximoEstado->value)->firstOrFail();

        AprovacaoPedido::updateOrCreate(
            ['id_pedido' => $pedido->id_pedido, 'papel_aprovador' => $papel->value],
            ['id_aprovador' => $user->id_utilizador, 'id_estado_pedido' => $estadoDestino->id_estado_pedido]
        );

        $resultado = $this->transicionar($user, $pedido, $proximoEstado);

        // Notificar o funcionário apenas quando o pedido fica definitivamente APROVADO
        if ($proximoEstado === EstadoPedidoEnum::APROVADO) {
            $resultado->loadMissing('utilizador');
            $resultado->utilizador?->notify(new PedidoAtualizadoNotification($resultado, 'aprovado'));
        }

        return $resultado;
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

    private function proximoEstado(EstadoPedidoEnum $atual, PapelAprovadorEnum $papel): EstadoPedidoEnum
    {
        if ($papel === PapelAprovadorEnum::COLEGA) {
            if ($atual !== EstadoPedidoEnum::EM_APROVACAO_COLEGA) {
                throw new WorkflowException('O colega só pode aprovar pedidos de Troca de Horário que aguardam a sua validação.');
            }
            return EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA;
        }

        if ($papel === PapelAprovadorEnum::DIRETORA_EXECUTIVA) {
            if ($atual !== EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA) {
                throw new WorkflowException('Só é possível aprovar pedidos que aguardam a decisão da Diretora Executiva.');
            }
            return EstadoPedidoEnum::APROVADO;
        }

        throw WorkflowException::transicaoInvalida($atual, EstadoPedidoEnum::APROVADO);
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
