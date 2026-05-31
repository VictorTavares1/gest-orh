<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Organizacao\OrganizacaoRequest;
use App\Http\Resources\OrganizacaoResource;
use App\Models\Organizacao;
use Illuminate\Http\JsonResponse;

class OrganizacaoController extends BaseController
{
    public function index(): JsonResponse
    {
        $organizacoes = Organizacao::with('setores')->orderBy('nome')->get();

        return $this->success(OrganizacaoResource::collection($organizacoes));
    }

    public function store(OrganizacaoRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['ativo'] = $data['ativo'] ?? true;

        $organizacao = Organizacao::create($data);

        return $this->created(new OrganizacaoResource($organizacao->load('setores')), 'Organização criada com sucesso.');
    }

    public function show(Organizacao $organizacao): JsonResponse
    {
        $organizacao->load('setores');

        return $this->success(new OrganizacaoResource($organizacao));
    }

    public function update(OrganizacaoRequest $request, Organizacao $organizacao): JsonResponse
    {
        $organizacao->update($request->validated());

        return $this->success(new OrganizacaoResource($organizacao->load('setores')), 'Organização atualizada com sucesso.');
    }

    public function destroy(Organizacao $organizacao): JsonResponse
    {
        if ($organizacao->setores()->exists()) {
            return $this->error('Não é possível eliminar uma organização com setores associados.', 422);
        }

        $organizacao->delete();

        return $this->success(null, 'Organização eliminada com sucesso.');
    }
}
