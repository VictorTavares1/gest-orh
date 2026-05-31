<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\CompSaidaAntecipadaRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\CompSaidaAntecipadaService;
use Illuminate\Http\JsonResponse;

class CompSaidaAntecipadaController extends BaseController
{
    public function __construct(private readonly CompSaidaAntecipadaService $service) {}

    public function store(CompSaidaAntecipadaRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Compensação de Saída Antecipada criado com sucesso.');
    }
}
