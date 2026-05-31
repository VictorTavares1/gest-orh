<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoFolgaAniversario;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class FolgaAniversarioService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::FOLGA_ANIVERSARIO->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoFolgaAniversario::create([
                'id_pedido'        => $pedido->id_pedido,
                'data_folga'       => $dados['data_folga'],
                'horario'          => $dados['horario'],
                'data_aniversario' => $dados['data_aniversario'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'folgaAniversario']);
        });
    }
}
