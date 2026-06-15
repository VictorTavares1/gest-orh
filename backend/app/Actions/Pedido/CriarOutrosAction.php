<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoOutros;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class CriarOutrosAction
{
    public function execute(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::OUTROS->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoOutros::create([
                'id_pedido'  => $pedido->id_pedido,
                'descricao'  => $dados['descricao'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'outros']);
        });
    }
}
