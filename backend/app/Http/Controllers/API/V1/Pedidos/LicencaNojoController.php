<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\LicencaNojoRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\LicencaNojoService;
use Illuminate\Http\JsonResponse;

class LicencaNojoController extends BaseController
{
    public function __construct(private readonly LicencaNojoService $service) {}

    public function store(LicencaNojoRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Licença de Nojo criado com sucesso.');
    }
}
