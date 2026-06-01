<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoMarcacaoFerias;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class CriarMarcacaoFeriasAction
{
    public function execute(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::MARCACAO_FERIAS->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoMarcacaoFerias::create([
                'id_pedido'   => $pedido->id_pedido,
                'id_periodo'  => $dados['id_periodo'],
                'data_inicio' => $dados['data_inicio'],
                'numero_dias' => $dados['numero_dias'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'marcacaoFerias']);
        });
    }
}
