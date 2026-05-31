<?php

namespace App\Services;

use App\Enums\EstadoPedidoEnum;
use App\Enums\PapelAprovadorEnum;
use App\Enums\TipoPedidoEnum;
use App\Exceptions\WorkflowException;
use App\Models\AprovacaoPedido;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;

class WorkflowService
{
    public function submeter(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if (!$estadoAtual->podeTransicionarPara(EstadoPedidoEnum::PENDENTE)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::PENDENTE);
        }

        $pedido->loadMissing('tipoPedido');
        $tipo = TipoPedidoEnum::fromLabel($pedido->tipoPedido->nome);

        // RASCUNHO → PENDENTE
        $this->transicionar($user, $pedido, EstadoPedidoEnum::PENDENTE);
        $pedido->refresh()->load(['estadoPedido', 'tipoPedido', 'utilizador']);

        // PENDENTE → fila de aprovação adequada
        $proximo = $tipo->requerColega()
            ? EstadoPedidoEnum::EM_APROVACAO_COLEGA
            : EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA;

        return $this->transicionar($user, $pedido, $proximo);
    }

    public function aprovar(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        $papel = $this->determinaPapel($user);
        $proximoEstado = $this->proximoEstadoParaAprovacao($estadoAtual, $papel);

        $estadoDestino = EstadoPedido::where('nome', $proximoEstado->value)->firstOrFail();

        AprovacaoPedido::updateOrCreate(
            ['id_pedido' => $pedido->id_pedido, 'papel_aprovador' => $papel->value],
            ['id_aprovador' => $user->id_utilizador, 'id_estado_pedido' => $estadoDestino->id_estado_pedido]
        );

        return $this->transicionar($user, $pedido, $proximoEstado);
    }

    public function rejeitar(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        if (!$estadoAtual->podeTransicionarPara(EstadoPedidoEnum::REJEITADO)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::REJEITADO);
        }

        $papel = $this->determinaPapel($user);

        // Colega só pode rejeitar enquanto o pedido está em aprovação de colega
        if ($papel === PapelAprovadorEnum::COLEGA && $estadoAtual !== EstadoPedidoEnum::EM_APROVACAO_COLEGA) {
            throw new WorkflowException('Não tem autoridade para rejeitar este pedido no estado atual.');
        }

        return $this->transicionar($user, $pedido, EstadoPedidoEnum::REJEITADO);
    }

    public function devolver(Utilizador $user, Pedido $pedido, ?string $comentario): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual !== EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::RASCUNHO);
        }

        // $comentario é aceite mas não persistido (Opção B acordada)
        return $this->transicionar($user, $pedido, EstadoPedidoEnum::RASCUNHO);
    }

    private function determinaPapel(Utilizador $user): PapelAprovadorEnum
    {
        if ($user->can('aprovacoes.diretora_executiva')) return PapelAprovadorEnum::DIRETORA_EXECUTIVA;
        if ($user->can('aprovacoes.colega'))            return PapelAprovadorEnum::COLEGA;

        throw new WorkflowException('Não tem autoridade para aprovar ou rejeitar pedidos.');
    }

    private function proximoEstadoParaAprovacao(EstadoPedidoEnum $atual, PapelAprovadorEnum $papel): EstadoPedidoEnum
    {
        // Colega só aprova pedidos em EM_APROVACAO_COLEGA → passa para executiva
        if ($papel === PapelAprovadorEnum::COLEGA) {
            if ($atual !== EstadoPedidoEnum::EM_APROVACAO_COLEGA) {
                throw new WorkflowException('O colega só pode aprovar pedidos de Troca de Horário que aguardam a sua validação.');
            }
            return EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA;
        }

        // Diretora executiva aprova pedidos em EM_APROVACAO_EXECUTIVA → APROVADO
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
