<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\TrocaHorarioRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\TrocaHorarioService;
use Illuminate\Http\JsonResponse;

class TrocaHorarioController extends BaseController
{
    public function __construct(private readonly TrocaHorarioService $service) {}

    public function store(TrocaHorarioRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Troca de Horário criado com sucesso.');
    }
}
