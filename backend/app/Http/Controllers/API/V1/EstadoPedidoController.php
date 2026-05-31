<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\EstadoPedidoResource;
use App\Models\EstadoPedido;
use Illuminate\Http\JsonResponse;

class EstadoPedidoController extends BaseController
{
    public function index(): JsonResponse
    {
        // A tabela tem duplicados — deduplica por nome
        $estados = EstadoPedido::orderBy('id_estado_pedido')
            ->get()
            ->unique('nome')
            ->values();

        return $this->success(EstadoPedidoResource::collection($estados));
    }
}
