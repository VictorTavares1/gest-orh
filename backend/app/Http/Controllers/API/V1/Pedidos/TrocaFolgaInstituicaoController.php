<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\TrocaFolgaInstituicaoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\TrocaFolgaInstituicaoService;
use Illuminate\Http\JsonResponse;

class TrocaFolgaInstituicaoController extends BaseController
{
    public function __construct(private readonly TrocaFolgaInstituicaoService $service) {}

    public function store(TrocaFolgaInstituicaoRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Troca de Folga com Instituição criado com sucesso.');
    }
}
