<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\AnexoResource;
use App\Models\Anexo;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnexoController extends BaseController
{
    public function index(Request $request, Pedido $pedido): JsonResponse
    {
        $pedido->load(['utilizador']);
        $this->authorize('view', $pedido);

        $pedido->load('anexos');

        return $this->success(AnexoResource::collection($pedido->anexos));
    }

    public function store(Request $request, Pedido $pedido): JsonResponse
    {
        abort(501, 'Upload de anexos disponível na Fase 5.');
    }

    public function destroy(Request $request, Anexo $anexo): JsonResponse
    {
        $pedido = $anexo->pedido()->with('utilizador')->first();
        $this->authorize('delete', $pedido);

        Storage::delete($anexo->caminho);
        $anexo->delete();

        return $this->noContent();
    }
}
