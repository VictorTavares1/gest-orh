<?php

namespace App\Services;

use App\Enums\EstadoPedidoEnum;
use App\Exceptions\WorkflowException;
use App\Models\EstadoPedido;
use App\Models\HistoricoPedido;
use App\Models\Pedido;
use App\Models\Utilizador;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PedidoService
{
    public function __construct(private readonly PedidoRepositoryInterface $pedidoRepository) {}

    public function listar(Utilizador $user, array $filtros = []): LengthAwarePaginator
    {
        $query = [];

        if (!$user->can('pedidos.ver.todos')) {
            if ($user->can('pedidos.ver.setor')) {
                $query['id_setor'] = $user->id_setor;
            } else {
                $query['id_utilizador'] = $user->id_utilizador;
            }
        }

        if (!empty($filtros['estado'])) {
            $query['estado'] = $filtros['estado'];
        }

        if (!empty($filtros['id_tipo_pedido'])) {
            $query['id_tipo_pedido'] = (int) $filtros['id_tipo_pedido'];
        }

        $perPage = isset($filtros['per_page']) ? (int) $filtros['per_page'] : 15;

        return $this->pedidoRepository->listarComFiltros($query, $perPage);
    }

    public function mostrar(int $id): Pedido
    {
        $pedido = $this->pedidoRepository->findComEspecializacao($id);

        if (!$pedido) {
            abort(404, 'Pedido não encontrado.');
        }

        return $pedido;
    }

    public function cancelar(Utilizador $user, Pedido $pedido): Pedido
    {
        // $pedido->estadoPedido->nome já é EstadoPedidoEnum devido ao cast no modelo
        $estadoAtual = $pedido->estadoPedido->nome;

        if ($estadoAtual->eTerminal()) {
            throw WorkflowException::pedidoTerminal();
        }

        if (!$estadoAtual->podeTransicionarPara(EstadoPedidoEnum::CANCELADO)) {
            throw WorkflowException::transicaoInvalida($estadoAtual, EstadoPedidoEnum::CANCELADO);
        }

        $estadoCancelado = EstadoPedido::where('nome', EstadoPedidoEnum::CANCELADO->value)->firstOrFail();

        HistoricoPedido::create([
            'id_pedido'          => $pedido->id_pedido,
            'id_utilizador_acao' => $user->id_utilizador,
            'id_estado_anterior' => $pedido->id_estado_pedido,
            'id_estado_novo'     => $estadoCancelado->id_estado_pedido,
            'data_alteracao'     => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $estadoCancelado->id_estado_pedido]);

        return $pedido->fresh(['estadoPedido', 'tipoPedido', 'utilizador']);
    }
}
