<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\JustificacaoFaltasRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\JustificacaoFaltasService;
use Illuminate\Http\JsonResponse;

class JustificacaoFaltasController extends BaseController
{
    public function __construct(private readonly JustificacaoFaltasService $service) {}

    public function store(JustificacaoFaltasRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Justificação de Faltas criado com sucesso.');
    }
}
