<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\InterrupcaoAtividadeRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\InterrupcaoAtividadeService;
use Illuminate\Http\JsonResponse;

class InterrupcaoAtividadeController extends BaseController
{
    public function __construct(private readonly InterrupcaoAtividadeService $service) {}

    public function store(InterrupcaoAtividadeRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Interrupção de Atividade criado com sucesso.');
    }
}
