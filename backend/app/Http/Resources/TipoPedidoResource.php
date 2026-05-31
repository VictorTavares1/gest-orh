<?php

namespace App\Http\Resources;

use App\Enums\TipoPedidoEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TipoPedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enum = TipoPedidoEnum::fromLabel($this->nome);

        return [
            'id_tipo_pedido' => $this->id_tipo_pedido,
            'nome'           => $this->nome,
            'slug'           => $enum->slug(),
            'ativo'          => $this->ativo,
        ];
    }
}
