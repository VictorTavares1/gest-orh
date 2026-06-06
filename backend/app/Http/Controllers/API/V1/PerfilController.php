<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Perfil\AtualizarPerfilRequest;
use App\Http\Resources\UtilizadorResource;
use Illuminate\Http\JsonResponse;

class PerfilController extends BaseController
{
    private array $eagerLoad = ['setor.organizacao', 'tipoUtilizador', 'roles', 'permissions'];

    public function update(AtualizarPerfilRequest $request): JsonResponse
    {
        $utilizador = $request->user();
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password_hash'] = bcrypt($data['password']);
            unset($data['password']);
        }

        $utilizador->update($data);

        return $this->success(
            new UtilizadorResource($utilizador->load($this->eagerLoad)),
            'Perfil atualizado com sucesso.'
        );
    }
}
