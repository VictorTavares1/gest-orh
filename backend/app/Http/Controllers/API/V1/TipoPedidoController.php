<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\TipoPedidoResource;
use App\Models\TipoPedido;
use Illuminate\Http\JsonResponse;

class TipoPedidoController extends BaseController
{
    public function index(): JsonResponse
    {
        // A tabela tem IDs 1-14 e 15-28 duplicados — deduplica por nome
        $tipos = TipoPedido::orderBy('id_tipo_pedido')
            ->get()
            ->unique('nome')
            ->values();

        return $this->success(TipoPedidoResource::collection($tipos));
    }
}
