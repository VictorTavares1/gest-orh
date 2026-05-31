<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class InterrupcaoAtividadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_trabalhada'    => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'horario_trabalhado' => ['required', 'date_format:H:i'],
            'data_folga'         => ['required', 'date_format:Y-m-d', 'after:today', 'after:data_trabalhada'],
            'horario_folga'      => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_trabalhada.required'       => 'A data trabalhada é obrigatória.',
            'data_trabalhada.date_format'    => 'A data deve estar no formato AAAA-MM-DD.',
            'data_trabalhada.before_or_equal'=> 'A data trabalhada não pode ser no futuro.',
            'horario_trabalhado.required'    => 'O horário trabalhado é obrigatório.',
            'horario_trabalhado.date_format' => 'O horário deve estar no formato HH:MM.',
            'data_folga.required'            => 'A data de folga é obrigatória.',
            'data_folga.date_format'         => 'A data deve estar no formato AAAA-MM-DD.',
            'data_folga.after'               => 'A data de folga deve ser uma data futura e posterior à data trabalhada.',
            'horario_folga.required'         => 'O horário de folga é obrigatório.',
            'horario_folga.date_format'      => 'O horário deve estar no formato HH:MM.',
        ];
    }
}
