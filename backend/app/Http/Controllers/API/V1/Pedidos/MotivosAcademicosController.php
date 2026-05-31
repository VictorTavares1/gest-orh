<?php

namespace App\Http\Controllers\API\V1\Pedidos;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Requests\Pedidos\MotivosAcademicosRequest;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\Pedidos\MotivosAcademicosService;
use Illuminate\Http\JsonResponse;

class MotivosAcademicosController extends BaseController
{
    public function __construct(private readonly MotivosAcademicosService $service) {}

    public function store(MotivosAcademicosRequest $request): JsonResponse
    {
        $this->authorize('create', Pedido::class);

        $pedido = $this->service->criar($request->user(), $request->validated());

        return $this->created(new PedidoResource($pedido), 'Pedido de Motivos Académicos criado com sucesso.');
    }
}
