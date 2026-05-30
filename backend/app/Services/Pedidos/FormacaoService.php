<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoFormacao;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class FormacaoService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::FORMACAO->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoFormacao::create([
                'id_pedido'     => $pedido->id_pedido,
                'data_formacao' => $dados['data_formacao'],
                'tema_formacao' => $dados['tema_formacao'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'formacao']);
        });
    }
}
