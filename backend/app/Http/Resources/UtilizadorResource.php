<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilizadorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_utilizador'   => $this->id_utilizador,
            'nome'            => $this->nome,
            'email'           => $this->email,
            'ativo'           => $this->ativo,
            'tipo_utilizador' => $this->whenLoaded('tipoUtilizador', fn () => [
                'id'   => $this->tipoUtilizador->id_tipo_utilizador,
                'nome' => $this->tipoUtilizador->nome,
            ]),
            'setor'           => $this->whenLoaded('setor', fn () => [
                'id'          => $this->setor->id_setor,
                'nome'        => $this->setor->nome,
                'organizacao' => $this->setor->relationLoaded('organizacao') ? [
                    'id'   => $this->setor->organizacao->id_organizacao,
                    'nome' => $this->setor->organizacao->nome,
                ] : null,
            ]),
            'roles'           => $this->getRoleNames(),
            'permissions'     => $this->getAllPermissions()->pluck('name'),
        ];
    }
}
