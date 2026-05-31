<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\FolgaAniversarioRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\FolgaAniversarioService;
use Illuminate\Http\JsonResponse;

class FolgaAniversarioController extends BaseController
{
    public function __construct(private readonly FolgaAniversarioService $service) {}

    public function store(FolgaAniversarioRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Folga de Aniversário criado com sucesso.');
    }
}
