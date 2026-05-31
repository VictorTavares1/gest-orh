<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\AssiduidadeRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\AssiduidadeService;
use Illuminate\Http\JsonResponse;

class AssiduidadeController extends BaseController
{
    public function __construct(private readonly AssiduidadeService $service) {}

    public function store(AssiduidadeRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Assiduidade criado com sucesso.');
    }
}
