<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\Utilizador\StoreUtilizadorRequest;
use App\Http\Requests\Utilizador\UpdateUtilizadorRequest;
use App\Http\Resources\UtilizadorResource;
use App\Models\TipoUtilizador;
use App\Models\Utilizador;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UtilizadorController extends BaseController
{
    private array $eagerLoad = ['setor.organizacao', 'tipoUtilizador', 'roles', 'permissions'];

    public function index(): JsonResponse
    {
        $utilizadores = Utilizador::with($this->eagerLoad)->orderBy('nome')->get();

        return $this->success(UtilizadorResource::collection($utilizadores));
    }

    public function store(StoreUtilizadorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $utilizador = DB::transaction(function () use ($data) {
            $utilizador = Utilizador::create([
                'nome'               => $data['nome'],
                'email'              => $data['email'],
                'password_hash'      => bcrypt($data['password']),
                'id_setor'           => $data['id_setor'],
                'id_tipo_utilizador' => $data['id_tipo_utilizador'],
                'ativo'              => $data['ativo'] ?? true,
            ]);

            $tipo = TipoUtilizador::findOrFail($utilizador->id_tipo_utilizador);
            $utilizador->assignRole($tipo->nome->roleSpatie());

            return $utilizador;
        });

        return $this->created(
            new UtilizadorResource($utilizador->load($this->eagerLoad)),
            'Utilizador criado com sucesso.'
        );
    }

    public function show(Utilizador $utilizador): JsonResponse
    {
        $utilizador->load($this->eagerLoad);

        return $this->success(new UtilizadorResource($utilizador));
    }

    public function update(UpdateUtilizadorRequest $request, Utilizador $utilizador): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $utilizador) {
            if (isset($data['password'])) {
                $data['password_hash'] = bcrypt($data['password']);
                unset($data['password']);
            }

            $utilizador->update($data);

            if (isset($data['id_tipo_utilizador'])) {
                $tipo = TipoUtilizador::findOrFail($utilizador->id_tipo_utilizador);
                $utilizador->syncRoles([$tipo->nome->roleSpatie()]);
            }
        });

        $utilizador->refresh()->load($this->eagerLoad);

        return $this->success(
            new UtilizadorResource($utilizador),
            'Utilizador atualizado com sucesso.'
        );
    }

    public function destroy(Utilizador $utilizador): JsonResponse
    {
        if ($utilizador->pedidos()->exists()) {
            return $this->error('Não é possível eliminar um utilizador com pedidos associados.', 422);
        }

        if ($utilizador->aprovacoes()->exists()) {
            return $this->error('Não é possível eliminar um utilizador com aprovações registadas.', 422);
        }

        $utilizador->delete();

        return $this->success(null, 'Utilizador eliminado com sucesso.');
    }

    public function ativar(Utilizador $utilizador): JsonResponse
    {
        $utilizador->update(['ativo' => true]);

        return $this->success(
            new UtilizadorResource($utilizador->load($this->eagerLoad)),
            'Utilizador ativado com sucesso.'
        );
    }

    public function desativar(Utilizador $utilizador): JsonResponse
    {
        $utilizador->update(['ativo' => false]);
        $utilizador->tokens()->delete();

        return $this->success(
            new UtilizadorResource($utilizador->load($this->eagerLoad)),
            'Utilizador desativado com sucesso.'
        );
    }
}
