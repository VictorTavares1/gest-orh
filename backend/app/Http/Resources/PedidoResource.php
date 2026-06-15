<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id_pedido'      => $this->id_pedido,
            'data_criacao'   => $this->data_criacao?->toIso8601String(),
            'estado'         => $this->whenLoaded('estadoPedido', fn() => [
                'id'    => $this->estadoPedido->id_estado_pedido,
                'nome'  => $this->estadoPedido->nome->value,
                'label' => $this->estadoPedido->nome->label(),
            ]),
            'tipo'           => $this->whenLoaded('tipoPedido', fn() => [
                'id'   => $this->tipoPedido->id_tipo_pedido,
                'nome' => $this->tipoPedido->nome,
            ]),
            'utilizador'     => $this->whenLoaded('utilizador', fn() => [
                'id'   => $this->utilizador->id_utilizador,
                'nome' => $this->utilizador->nome,
            ]),
            'aprovacoes'     => $this->whenLoaded('aprovacoes', fn() => $this->aprovacoes->map(fn($a) => [
                'id'          => $a->id_aprovacao,
                'papel'       => $a->papel_aprovador->value,
                'papel_label' => $a->papel_aprovador->label(),
                'aprovador'   => $a->relationLoaded('aprovador') ? [
                    'id'   => $a->aprovador->id_utilizador,
                    'nome' => $a->aprovador->nome,
                ] : null,
            ])),
            'historico'      => $this->whenLoaded('historico', fn() => HistoricoResource::collection($this->historico)),
            'especializacao' => $this->especializacaoCarregada(),
        ];
    }

    private function especializacaoCarregada(): mixed
    {
        $relacoes = [
            'horasExtras', 'justificacaoFaltas', 'marcacaoFerias', 'alteracaoFerias',
            'trocaHorario', 'trocaFolgaInstituicao', 'interrupcaoAtividade', 'folgaAniversario',
            'assiduidade', 'licencaNojo', 'formacao', 'motivosAcademicos',
            'compEntradaTardia', 'compSaidaAntecipada', 'outros',
        ];

        foreach ($relacoes as $relacao) {
            if ($this->relationLoaded($relacao) && $this->{$relacao} !== null) {
                return $this->{$relacao}->toArray();
            }
        }

        return null;
    }
}
