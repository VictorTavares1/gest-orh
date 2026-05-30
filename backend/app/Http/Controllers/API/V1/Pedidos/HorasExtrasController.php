<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\HorasExtrasRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\HorasExtrasService;
use Illuminate\Http\JsonResponse;

class HorasExtrasController extends BaseController
{
    public function __construct(private readonly HorasExtrasService $service) {}

    public function store(HorasExtrasRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Horas Extras criado com sucesso.');
    }
}
