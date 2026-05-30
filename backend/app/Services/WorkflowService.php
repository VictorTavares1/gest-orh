<?php

namespace App\Services;

use App\Enums\EstadoPedidoEnum;
use App\Enums\PapelAprovadorEnum;
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

        return $this->transicionar($user, $pedido, EstadoPedidoEnum::PENDENTE);
    }

    public function aprovar(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        $papel = $this->determinaPapel($user);
        $proximoEstado = $this->determinaProximoEstado($estadoAtual, $papel);

        if (!$estadoAtual->podeTransicionarPara($proximoEstado)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, $proximoEstado);
        }

        $estadoDestino = EstadoPedido::where('nome', $proximoEstado->value)->firstOrFail();

        // uk_pedido_papel impede duplicados — actualiza se o papel já tiver aprovado antes
        AprovacaoPedido::updateOrCreate(
            ['id_pedido' => $pedido->id_pedido, 'papel_aprovador' => $papel],
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

        return $this->transicionar($user, $pedido, EstadoPedidoEnum::REJEITADO);
    }

    private function determinaPapel(Utilizador $user): PapelAprovadorEnum
    {
        if ($user->can('aprovacoes.diretora_executiva')) return PapelAprovadorEnum::DIRETORA_EXECUTIVA;
        if ($user->can('aprovacoes.diretor_tecnico'))   return PapelAprovadorEnum::DIRETOR_TECNICO;
        if ($user->can('aprovacoes.colega'))            return PapelAprovadorEnum::COLEGA;

        throw new WorkflowException('Não tem autoridade para aprovar este pedido.');
    }

    private function determinaProximoEstado(EstadoPedidoEnum $atual, PapelAprovadorEnum $papel): EstadoPedidoEnum
    {
        if ($atual === EstadoPedidoEnum::PENDENTE) {
            return match ($papel) {
                PapelAprovadorEnum::COLEGA             => EstadoPedidoEnum::EM_APROVACAO_COLEGA,
                PapelAprovadorEnum::DIRETOR_TECNICO    => EstadoPedidoEnum::EM_APROVACAO_DIRETOR,
                // Executiva pode saltar o nível de colega mas não o de diretor (state machine não permite PENDENTE→EM_APROVACAO_EXECUTIVA)
                PapelAprovadorEnum::DIRETORA_EXECUTIVA => EstadoPedidoEnum::EM_APROVACAO_DIRETOR,
            };
        }

        if ($atual === EstadoPedidoEnum::EM_APROVACAO_COLEGA) {
            return EstadoPedidoEnum::EM_APROVACAO_DIRETOR;
        }

        if ($atual === EstadoPedidoEnum::EM_APROVACAO_DIRETOR) {
            return match ($papel) {
                PapelAprovadorEnum::DIRETOR_TECNICO    => EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA,
                PapelAprovadorEnum::DIRETORA_EXECUTIVA => EstadoPedidoEnum::APROVADO,
                default => throw WorkflowException::transicaoInvalida($atual, EstadoPedidoEnum::APROVADO),
            };
        }

        if ($atual === EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA && $papel === PapelAprovadorEnum::DIRETORA_EXECUTIVA) {
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
