<?php

namespace App\Repositories;

use App\Models\Pedido;
use App\Repositories\Contracts\PedidoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PedidoRepository extends BaseRepository implements PedidoRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Pedido());
    }

    public function listarComFiltros(array $filtros, int $perPage = 15): LengthAwarePaginator
    {
        $query = Pedido::with(['utilizador', 'tipoPedido', 'estadoPedido'])
            ->orderByDesc('data_criacao');

        if (isset($filtros['id_utilizador'])) {
            $query->where('id_utilizador', $filtros['id_utilizador']);
        }

        if (isset($filtros['id_setor'])) {
            $query->whereHas('utilizador', fn($q) => $q->where('id_setor', $filtros['id_setor']));
        }

        if (isset($filtros['estado'])) {
            $query->whereHas('estadoPedido', fn($q) => $q->where('nome', $filtros['estado']));
        }

        if (isset($filtros['id_tipo_pedido'])) {
            $query->where('id_tipo_pedido', $filtros['id_tipo_pedido']);
        }

        return $query->paginate($perPage);
    }

    public function findComEspecializacao(int $id): ?Pedido
    {
        $pedido = Pedido::with([
            'utilizador.setor',
            'tipoPedido',
            'estadoPedido',
            'aprovacoes.aprovador',
            'historico.estadoNovo',
            'historico.estadoAnterior',
            'historico.utilizadorAcao',
        ])->find($id);

        if ($pedido && ($relacao = $pedido->relacaoEspecializacao())) {
            $pedido->load($relacao);
        }

        return $pedido;
    }
}
