<?php

namespace App\Services;

use App\Actions\Pedido\CancelarPedidoAction;
use App\Models\Pedido;
use App\Models\Utilizador;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PedidoService
{
    public function __construct(
        private readonly PedidoRepositoryInterface $pedidoRepository,
        private readonly CancelarPedidoAction      $cancelarAction,
    ) {}

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

        if (!empty($filtros['mes']) && !empty($filtros['ano'])) {
            $query['mes'] = (int) $filtros['mes'];
            $query['ano'] = (int) $filtros['ano'];
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
        return $this->cancelarAction->execute($user, $pedido);
    }
}
