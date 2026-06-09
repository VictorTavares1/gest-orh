<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class CompSaidaAntecipadaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_saida_antecipada' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'horario'               => ['required', 'date_format:H:i'],
            'num_horas_extras'      => ['required', 'numeric', 'min:0.25', 'max:24'],
            'data_horas_extras'     => ['required', 'date_format:Y-m-d', 'after_or_equal:data_saida_antecipada'],
            'motivo'                => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_saida_antecipada.required'        => 'A data de saída antecipada é obrigatória.',
            'data_saida_antecipada.date_format'     => 'A data deve estar no formato AAAA-MM-DD.',
            'data_saida_antecipada.before_or_equal' => 'A data de saída antecipada não pode ser no futuro.',
            'horario.required'                      => 'O horário de saída é obrigatório.',
            'horario.date_format'                   => 'O horário deve estar no formato HH:MM.',
            'num_horas_extras.required'             => 'O número de horas extras é obrigatório.',
            'num_horas_extras.numeric'              => 'O número de horas deve ser um valor numérico.',
            'num_horas_extras.min'                  => 'O mínimo é 0.25 horas (15 minutos).',
            'num_horas_extras.max'                  => 'O máximo é 24 horas.',
            'data_horas_extras.required'            => 'A data das horas extras é obrigatória.',
            'data_horas_extras.date_format'         => 'A data deve estar no formato AAAA-MM-DD.',
            'data_horas_extras.after_or_equal'      => 'A data das horas extras não pode ser anterior à data de saída antecipada.',
            'motivo.required'                       => 'O motivo é obrigatório.',
            'motivo.min'                            => 'O motivo deve ter pelo menos 3 caracteres.',
            'motivo.max'                            => 'O motivo não pode exceder 1000 caracteres.',
        ];
    }
}
