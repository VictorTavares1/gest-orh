<?php

namespace App\Repositories\Contracts;

use App\Models\Pedido;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PedidoRepositoryInterface extends BaseRepositoryInterface
{
    public function listarComFiltros(array $filtros, int $perPage): LengthAwarePaginator;
    public function findComEspecializacao(int $id): ?Pedido;
}
