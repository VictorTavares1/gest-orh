<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\MarcacaoFeriasRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\MarcacaoFeriasService;
use Illuminate\Http\JsonResponse;

class MarcacaoFeriasController extends BaseController
{
    public function __construct(private readonly MarcacaoFeriasService $service) {}

    public function store(MarcacaoFeriasRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Marcação de Férias criado com sucesso.');
    }
}
