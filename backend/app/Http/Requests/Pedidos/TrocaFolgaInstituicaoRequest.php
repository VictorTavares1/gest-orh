<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class TrocaFolgaInstituicaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_original'    => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'horario_original' => ['required', 'date_format:H:i'],
            'nova_data'        => ['required', 'date_format:Y-m-d', 'after:data_original', 'after_or_equal:today'],
            'motivo'           => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_original.required'       => 'A data original é obrigatória.',
            'data_original.date_format'    => 'A data original deve estar no formato AAAA-MM-DD.',
            'data_original.before_or_equal' => 'A data original não pode ser no futuro.',
            'horario_original.required'    => 'O horário original é obrigatório.',
            'horario_original.date_format' => 'O horário deve estar no formato HH:MM.',
            'nova_data.required'           => 'A nova data é obrigatória.',
            'nova_data.date_format'        => 'A nova data deve estar no formato AAAA-MM-DD.',
            'nova_data.after'              => 'A nova data deve ser posterior à data original.',
            'nova_data.after_or_equal'     => 'A nova data deve ser hoje ou uma data futura.',
            'motivo.required'              => 'O motivo é obrigatório.',
            'motivo.min'                   => 'O motivo deve ter pelo menos 10 caracteres.',
            'motivo.max'                   => 'O motivo não pode exceder 1000 caracteres.',
        ];
    }
}
