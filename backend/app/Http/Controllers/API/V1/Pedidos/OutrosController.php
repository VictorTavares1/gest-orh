<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\OutrosRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\OutrosService;
use Illuminate\Http\JsonResponse;

class OutrosController extends BaseController
{
    public function __construct(private readonly OutrosService $service) {}

    public function store(OutrosRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido criado com sucesso.');
    }
}
