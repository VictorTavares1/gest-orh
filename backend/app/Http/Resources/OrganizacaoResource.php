<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_organizacao' => $this->id_organizacao,
            'nome'           => $this->nome,
            'ativo'          => $this->ativo,
            'setores'        => SetorResource::collection($this->whenLoaded('setores')),
        ];
    }
}
