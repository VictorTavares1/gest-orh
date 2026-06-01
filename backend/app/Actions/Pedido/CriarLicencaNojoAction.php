<?php

namespace App\Actions\Pedido;

use App\Enums\EstadoPedidoEnum;
use App\Enums\TipoPedidoEnum;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\PedidoLicencaNojo;
use App\Models\TipoPedido;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class CriarLicencaNojoAction
{
    public function execute(Utilizador $user, array $dados): Pedido
    {
        return DB::transaction(function () use ($user, $dados) {
            $tipo   = TipoPedido::where('nome', TipoPedidoEnum::LICENCA_NOJO->label())->firstOrFail();
            $estado = EstadoPedido::where('nome', EstadoPedidoEnum::RASCUNHO->value)->firstOrFail();

            $pedido = Pedido::create([
                'id_utilizador'    => $user->id_utilizador,
                'id_tipo_pedido'   => $tipo->id_tipo_pedido,
                'id_estado_pedido' => $estado->id_estado_pedido,
                'data_criacao'     => now(),
            ]);

            PedidoLicencaNojo::create([
                'id_pedido'       => $pedido->id_pedido,
                'dias_ausencia'   => $dados['dias_ausencia'],
                'nome_falecido'   => $dados['nome_falecido'],
                'grau_parentesco' => $dados['grau_parentesco'],
            ]);

            return $pedido->load(['tipoPedido', 'estadoPedido', 'utilizador', 'licencaNojo']);
        });
    }
}
