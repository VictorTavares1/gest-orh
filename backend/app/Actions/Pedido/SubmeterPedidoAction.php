<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Exceptions\WorkflowException;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;
use App\Notifications\PedidoSubmetidoNotification;
use Illuminate\Support\Facades\DB;

class SubmeterPedidoAction
{
    public function execute(Utilizador $user, Pedido $pedido): Pedido
    {
        $estadoAtual = $pedido->estadoPedido->nome;

        if (! $estadoAtual->podeTransicionarPara(EstadoPedidoEnum::PENDENTE)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::PENDENTE);
        }

        $pedido->loadMissing('tipoPedido');
        $tipo = TipoPedidoEnum::fromLabel($pedido->tipoPedido->nome);

        $resultado = DB::transaction(function () use ($user, $pedido, $tipo) {
            $this->transicionar($user, $pedido, EstadoPedidoEnum::PENDENTE);
            $pedido->refresh()->load(['estadoPedido', 'tipoPedido', 'utilizador']);

            $proximo = $tipo->requerColega()
                ? EstadoPedidoEnum::EM_APROVACAO_COLEGA
                : EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA;

            return $this->transicionar($user, $pedido, $proximo);
        });

        // Notificar quem tem poder de aprovação executiva (diretora ou substituta)
        $aprovadores = Utilizador::permission('aprovacoes.diretora_executiva')->where('ativo', true)->get();
        foreach ($aprovadores as $aprovador) {
            $aprovador->notify(new PedidoSubmetidoNotification($resultado));
        }

        return $resultado;
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
