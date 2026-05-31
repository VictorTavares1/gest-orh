<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\CompEntradaTardiaRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\CompEntradaTardiaService;
use Illuminate\Http\JsonResponse;

class CompEntradaTardiaController extends BaseController
{
    public function __construct(private readonly CompEntradaTardiaService $service) {}

    public function store(CompEntradaTardiaRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Compensação de Entrada Tardia criado com sucesso.');
    }
}
