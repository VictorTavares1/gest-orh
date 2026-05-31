<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class FolgaAniversarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_folga'       => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'horario'          => ['required', 'date_format:H:i'],
            'data_aniversario' => ['required', 'date_format:Y-m-d', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_folga.required'         => 'A data da folga é obrigatória.',
            'data_folga.date_format'      => 'A data da folga deve estar no formato AAAA-MM-DD.',
            'data_folga.after_or_equal'   => 'A data da folga deve ser hoje ou uma data futura.',
            'horario.required'            => 'O horário é obrigatório.',
            'horario.date_format'         => 'O horário deve estar no formato HH:MM.',
            'data_aniversario.required'   => 'A data de aniversário é obrigatória.',
            'data_aniversario.date_format' => 'A data de aniversário deve estar no formato AAAA-MM-DD.',
            'data_aniversario.before'     => 'A data de aniversário deve ser uma data passada.',
        ];
    }
}
