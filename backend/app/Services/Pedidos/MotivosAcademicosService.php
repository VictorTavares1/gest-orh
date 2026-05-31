<?php

namespace App\Services\Pedidos;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoMotivosAcademicos;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class MotivosAcademicosService
{
    public function criar(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::MOTIVOS_ACADEMICOS->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoMotivosAcademicos::create([
                'id_pedido'        => $pedido->id_pedido,
                'data_ausencia'    => $dados['data_ausencia'],
                'motivo_academico' => $dados['motivo_academico'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'motivosAcademicos']);
        });
    }
}
