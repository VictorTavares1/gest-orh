<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_periodo'  => $this->id_periodo,
            'data_inicio' => $this->data_inicio?->format('Y-m-d'),
            'data_fim'    => $this->data_fim?->format('Y-m-d'),
        ];
    }
}
