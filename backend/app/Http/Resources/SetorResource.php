<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_setor'    => $this->id_setor,
            'nome'        => $this->nome,
            'organizacao' => $this->whenLoaded('organizacao', fn () => [
                'id_organizacao' => $this->organizacao->id_organizacao,
                'nome'           => $this->organizacao->nome,
            ]),
        ];
    }
}
