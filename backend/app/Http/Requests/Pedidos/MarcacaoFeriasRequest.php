<?php

namespace App\Http\Requests\Pedidos;

use App\Models\Periodo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarcacaoFeriasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_periodo'  => ['required', 'integer', Rule::exists('periodo', 'id_periodo')],
            'data_inicio' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'numero_dias' => ['required', 'integer', 'min:1', 'max:22'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['id_periodo', 'data_inicio', 'numero_dias'])) {
                return;
            }

            $periodo = Periodo::find($this->input('id_periodo'));
            $inicio  = $this->input('data_inicio');

            if ($periodo && ($inicio < $periodo->data_inicio->format('Y-m-d') || $inicio > $periodo->data_fim->format('Y-m-d'))) {
                $validator->errors()->add('data_inicio', 'A data de início deve estar dentro do período de férias selecionado.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'id_periodo.required'  => 'O período é obrigatório.',
            'id_periodo.integer'   => 'O período deve ser um identificador válido.',
            'id_periodo.exists'    => 'O período selecionado não existe.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date_format' => 'A data deve estar no formato AAAA-MM-DD.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
            'numero_dias.required' => 'O número de dias é obrigatório.',
            'numero_dias.integer'  => 'O número de dias deve ser um valor inteiro.',
            'numero_dias.min'      => 'O mínimo é 1 dia de férias.',
            'numero_dias.max'      => 'O máximo é 22 dias de férias por pedido.',
        ];
    }
}
