<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoCompEntradaTardia;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class CompEntradaTardiaService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::COMP_ENTRADA_TARDIA->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoCompEntradaTardia::create([
                'id_pedido'           => $pedido->id_pedido,
                'data_entrada_tardia' => $dados['data_entrada_tardia'],
                'horario'             => $dados['horario'],
                'num_horas_extras'    => $dados['num_horas_extras'],
                'data_horas_extras'   => $dados['data_horas_extras'],
                'motivo'              => $dados['motivo'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'compEntradaTardia']);
        });
    }
}
