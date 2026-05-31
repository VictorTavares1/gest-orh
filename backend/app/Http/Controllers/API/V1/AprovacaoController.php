<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\PedidoResource;
use App\Models\Pedido;
use App\Services\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AprovacaoController extends BaseController
{
    public function __construct(private readonly WorkflowService $workflowService) {}

    public function submeter(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);
        $this->authorize('update', $pedido);

        $atualizado = $this->workflowService->submeter($request->user(), $pedido);

        return $this->success(new PedidoResource($atualizado), 'Pedido submetido com sucesso.');
    }

    public function aprovar(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);
        $this->authorize('aprovar', $pedido);

        $atualizado = $this->workflowService->aprovar($request->user(), $pedido);

        return $this->success(new PedidoResource($atualizado), 'Pedido aprovado com sucesso.');
    }

    public function rejeitar(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);
        $this->authorize('rejeitar', $pedido);

        $atualizado = $this->workflowService->rejeitar($request->user(), $pedido);

        return $this->success(new PedidoResource($atualizado), 'Pedido rejeitado.');
    }

    public function devolver(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);
        $this->authorize('devolver', $pedido);

        $comentario = $request->input('comentario');

        $atualizado = $this->workflowService->devolver($request->user(), $pedido, $comentario);

        return $this->success(new PedidoResource($atualizado), 'Pedido devolvido ao colaborador para revisão.');
    }
}
