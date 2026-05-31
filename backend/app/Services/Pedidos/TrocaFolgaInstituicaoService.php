<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoTrocaFolgaInstituicao;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class TrocaFolgaInstituicaoService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::TROCA_FOLGA_INSTITUICAO->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoTrocaFolgaInstituicao::create([
                'id_pedido'        => $pedido->id_pedido,
                'data_original'    => $dados['data_original'],
                'horario_original' => $dados['horario_original'],
                'nova_data'        => $dados['nova_data'],
                'motivo'           => $dados['motivo'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'trocaFolgaInstituicao']);
        });
    }
}
