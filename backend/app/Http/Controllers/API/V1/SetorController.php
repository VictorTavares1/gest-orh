<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Setor\SetorRequest;
use App\Http\Resources\SetorResource;
use App\Models\Setor;
use Illuminate\Http\JsonResponse;

class SetorController extends BaseController
{
    public function index(): JsonResponse
    {
        $setores = Setor::with('organizacao')->orderBy('nome')->get();

        return $this->success(SetorResource::collection($setores));
    }

    public function store(SetorRequest $request): JsonResponse
    {
        $setor = Setor::create($request->validated());

        return $this->created(new SetorResource($setor->load('organizacao')), 'Setor criado com sucesso.');
    }

    public function show(Setor $setor): JsonResponse
    {
        $setor->load('organizacao');

        return $this->success(new SetorResource($setor));
    }

    public function update(SetorRequest $request, Setor $setor): JsonResponse
    {
        $setor->update($request->validated());

        return $this->success(new SetorResource($setor->load('organizacao')), 'Setor atualizado com sucesso.');
    }

    public function destroy(Setor $setor): JsonResponse
    {
        if ($setor->utilizadores()->exists()) {
            return $this->error('Não é possível eliminar um setor com utilizadores associados.', 422);
        }

        $setor->delete();

        return $this->success(null, 'Setor eliminado com sucesso.');
    }
}
