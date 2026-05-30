<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\AnexoResource;
use App\Models\Anexo;
use App\Models\Pedido;
use App\Services\AnexoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnexoController extends BaseController
{
    public function __construct(private readonly AnexoService $anexoService) {}

    public function index(Pedido $pedido): JsonResponse
    {
        $pedido->load(['utilizador']);
        $this->authorize('view', $pedido);

        $pedido->load('anexos');

        return $this->success(AnexoResource::collection($pedido->anexos));
    }

    public function store(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['estadoPedido', 'utilizador']);
        $this->authorize('update', $pedido);

        $request->validate([
            'ficheiro' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'extensions:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
        ]);

        $anexo = $this->anexoService->upload($pedido, $request->file('ficheiro'));

        return $this->created(new AnexoResource($anexo), 'Anexo adicionado com sucesso.');
    }

    public function download(Anexo $anexo): StreamedResponse
    {
        $pedido = $anexo->pedido()->with(['utilizador'])->firstOrFail();
        $this->authorize('view', $pedido);

        return $this->anexoService->download($anexo);
    }

    public function destroy(Anexo $anexo): JsonResponse
    {
        $pedido = $anexo->pedido()->with(['estadoPedido', 'utilizador'])->firstOrFail();
        $this->authorize('update', $pedido);

        $this->anexoService->remover($anexo);

        return $this->noContent();
    }
}
