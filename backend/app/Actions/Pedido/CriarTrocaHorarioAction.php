<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoTrocaHorario;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class CriarTrocaHorarioAction
{
    public function execute(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::TROCA_HORARIO->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoTrocaHorario::create([
                'id_pedido'          => $pedido->id_pedido,
                'id_colega'          => $dados['id_colega'],
                'id_setor_colega'    => $dados['id_setor_colega'],
                'data_troca'         => $dados['data_troca'],
                'horario_antigo_ini' => $dados['horario_antigo_ini'],
                'horario_antigo_fim' => $dados['horario_antigo_fim'],
                'horario_novo_ini'   => $dados['horario_novo_ini'],
                'horario_novo_fim'   => $dados['horario_novo_fim'],
                'motivo'             => $dados['motivo'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'trocaHorario']);
        });
    }
}
