<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstadoPedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_estado_pedido' => $this->id_estado_pedido,
            'nome'             => $this->nome->value,
        ];
    }
}
