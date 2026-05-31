<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Periodo\PeriodoRequest;
use App\Http\Resources\PeriodoResource;
use App\Models\Periodo;
use Illuminate\Http\JsonResponse;

class PeriodoController extends BaseController
{
    public function index(): JsonResponse
    {
        $periodos = Periodo::orderBy('data_inicio')->get();

        return $this->success(PeriodoResource::collection($periodos));
    }

    public function store(PeriodoRequest $request): JsonResponse
    {
        $periodo = Periodo::create($request->validated());

        return $this->created(new PeriodoResource($periodo), 'Período criado com sucesso.');
    }

    public function show(Periodo $periodo): JsonResponse
    {
        return $this->success(new PeriodoResource($periodo));
    }

    public function update(PeriodoRequest $request, Periodo $periodo): JsonResponse
    {
        $periodo->update($request->validated());

        return $this->success(new PeriodoResource($periodo), 'Período atualizado com sucesso.');
    }

    public function destroy(Periodo $periodo): JsonResponse
    {
        if ($periodo->marcacoesFerias()->exists() || $periodo->alteracoesFerias()->exists()) {
            return $this->error('Não é possível eliminar um período com pedidos associados.', 422);
        }

        $periodo->delete();

        return $this->success(null, 'Período eliminado com sucesso.');
    }
}
