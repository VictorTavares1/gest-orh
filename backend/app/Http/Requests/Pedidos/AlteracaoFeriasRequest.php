<?php

namespace App\Http\Requests\Pedidos;

use App\Models\Periodo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlteracaoFeriasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_periodo_atual' => ['required', 'integer', Rule::exists('periodo', 'id_periodo')],
            'novo_inicio'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'novo_fim'         => ['required', 'date_format:Y-m-d', 'after:novo_inicio'],
            'numero_dias'      => ['required', 'integer', 'min:1', 'max:22'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['id_periodo_atual', 'novo_inicio', 'novo_fim'])) {
                return;
            }

            $periodo = Periodo::find($this->input('id_periodo_atual'));
            if (!$periodo) {
                return;
            }

            $periodoIni = $periodo->data_inicio->format('Y-m-d');
            $periodoFim = $periodo->data_fim->format('Y-m-d');
            $novoInicio = $this->input('novo_inicio');
            $novoFim    = $this->input('novo_fim');

            if ($novoInicio < $periodoIni || $novoInicio > $periodoFim) {
                $validator->errors()->add('novo_inicio', 'O novo início deve estar dentro do período selecionado.');
            }

            if ($novoFim < $periodoIni || $novoFim > $periodoFim) {
                $validator->errors()->add('novo_fim', 'O novo fim deve estar dentro do período selecionado.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_periodo_atual.required' => 'O período atual é obrigatório.',
            'id_periodo_atual.integer'  => 'O período deve ser um identificador válido.',
            'id_periodo_atual.exists'   => 'O período selecionado não existe.',
            'novo_inicio.required'      => 'A nova data de início é obrigatória.',
            'novo_inicio.date_format'   => 'A data deve estar no formato AAAA-MM-DD.',
            'novo_inicio.after_or_equal' => 'A nova data de início deve ser hoje ou uma data futura.',
            'novo_fim.required'         => 'A nova data de fim é obrigatória.',
            'novo_fim.date_format'      => 'A data deve estar no formato AAAA-MM-DD.',
            'novo_fim.after'            => 'A nova data de fim deve ser posterior à data de início.',
            'numero_dias.required'      => 'O número de dias é obrigatório.',
            'numero_dias.integer'       => 'O número de dias deve ser um valor inteiro.',
            'numero_dias.min'           => 'O mínimo é 1 dia de férias.',
            'numero_dias.max'           => 'O máximo é 22 dias de férias por pedido.',
        ];
    }
}
