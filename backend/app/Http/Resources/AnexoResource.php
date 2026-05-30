<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnexoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_anexo'      => $this->id_anexo,
            'nome_original' => basename($this->caminho),
            'url_download'  => route('v1.anexos.download', $this->id_anexo),
        ];
    }
}
