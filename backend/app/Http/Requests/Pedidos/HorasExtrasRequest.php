<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class HorasExtrasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_horas'   => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'numero_horas' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'motivo'       => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_horas.required'        => 'A data das horas extras é obrigatória.',
            'data_horas.date_format'     => 'A data deve estar no formato AAAA-MM-DD.',
            'data_horas.before_or_equal' => 'A data das horas extras não pode ser no futuro.',
            'numero_horas.required'      => 'O número de horas é obrigatório.',
            'numero_horas.numeric'       => 'O número de horas deve ser um valor numérico.',
            'numero_horas.min'           => 'O mínimo é 0.25 horas (15 minutos).',
            'numero_horas.max'           => 'O máximo é 24 horas por dia.',
            'motivo.required'            => 'O motivo é obrigatório.',
            'motivo.min'                 => 'O motivo deve ter pelo menos 10 caracteres.',
            'motivo.max'                 => 'O motivo não pode exceder 1000 caracteres.',
        ];
    }
}
