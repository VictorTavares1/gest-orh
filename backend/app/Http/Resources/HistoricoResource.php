<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HistoricoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_historico'    => $this->id_historico,
            'data_alteracao'  => $this->data_alteracao?->toIso8601String(),
            'utilizador_acao' => $this->whenLoaded('utilizadorAcao', fn() => [
                'id'   => $this->utilizadorAcao->id_utilizador,
                'nome' => $this->utilizadorAcao->nome,
            ]),
            'estado_anterior' => $this->whenLoaded('estadoAnterior', fn() => $this->estadoAnterior ? [
                'id'    => $this->estadoAnterior->id_estado_pedido,
                'nome'  => $this->estadoAnterior->nome->value,
                'label' => $this->estadoAnterior->nome->label(),
            ] : null),
            'estado_novo'     => $this->whenLoaded('estadoNovo', fn() => [
                'id'    => $this->estadoNovo->id_estado_pedido,
                'nome'  => $this->estadoNovo->nome->value,
                'label' => $this->estadoNovo->nome->label(),
            ]),
        ];
    }
}
