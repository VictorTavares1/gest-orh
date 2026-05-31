<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoInterrupcaoAtividade;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class InterrupcaoAtividadeService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::INTERRUPCAO_ATIVIDADE->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoInterrupcaoAtividade::create([
                'id_pedido'          => $pedido->id_pedido,
                'data_trabalhada'    => $dados['data_trabalhada'],
                'horario_trabalhado' => $dados['horario_trabalhado'],
                'data_folga'         => $dados['data_folga'],
                'horario_folga'      => $dados['horario_folga'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'interrupcaoAtividade']);
        });
    }
}
