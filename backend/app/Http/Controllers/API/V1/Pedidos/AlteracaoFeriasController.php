<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\AlteracaoFeriasRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\AlteracaoFeriasService;
use Illuminate\Http\JsonResponse;

class AlteracaoFeriasController extends BaseController
{
    public function __construct(private readonly AlteracaoFeriasService $service) {}

    public function store(AlteracaoFeriasRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Alteração de Férias criado com sucesso.');
    }
}
