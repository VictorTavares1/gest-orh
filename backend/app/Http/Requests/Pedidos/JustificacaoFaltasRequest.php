<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class JustificacaoFaltasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_falta' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'hora_falta' => ['required', 'date_format:H:i'],
            'motivo'     => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_falta.required'        => 'A data da falta é obrigatória.',
            'data_falta.date_format'     => 'A data deve estar no formato AAAA-MM-DD.',
            'data_falta.before_or_equal' => 'A data da falta não pode ser no futuro.',
            'hora_falta.required'        => 'A hora da falta é obrigatória.',
            'hora_falta.date_format'     => 'A hora deve estar no formato HH:MM.',
            'motivo.required'            => 'O motivo é obrigatório.',
            'motivo.min'                 => 'O motivo deve ter pelo menos 3 caracteres.',
            'motivo.max'                 => 'O motivo não pode exceder 1000 caracteres.',
        ];
    }
}
