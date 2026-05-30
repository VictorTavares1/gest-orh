<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\HistoricoResource;
use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends BaseController
{
    public function __construct(private readonly PedidoService $pedidoService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Pedido::class);

        $paginator = $this->pedidoService->listar(
            $request->user(),
            $request->only(['estado', 'id_tipo_pedido', 'per_page'])
        );

        return $this->paginated(PedidoResource::collection($paginator));
    }

    public function show(Request $request, int $pedido): JsonResponse
    {
        $model = $this->pedidoService->mostrar($pedido);

        $this->authorize('view', $model);

        return $this->success(new PedidoResource($model));
    }

    public function destroy(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);

        $this->authorize('delete', $pedido);

        $atualizado = $this->pedidoService->cancelar($request->user(), $pedido);

        return $this->success(new PedidoResource($atualizado), 'Pedido cancelado com sucesso.');
    }

    public function historico(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['utilizador']);
        $this->authorize('view', $pedido);

        $pedido->load(['historico.utilizadorAcao', 'historico.estadoAnterior', 'historico.estadoNovo']);

        return $this->success(HistoricoResource::collection($pedido->historico));
    }
}
