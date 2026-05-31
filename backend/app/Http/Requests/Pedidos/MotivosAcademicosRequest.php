<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class MotivosAcademicosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_ausencia'    => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'motivo_academico' => ['required', 'string', 'min:5', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_ausencia.required'       => 'A data de ausência é obrigatória.',
            'data_ausencia.date_format'    => 'A data deve estar no formato AAAA-MM-DD.',
            'data_ausencia.after_or_equal' => 'A data de ausência não pode ser no passado.',
            'motivo_academico.required'    => 'O motivo académico é obrigatório.',
            'motivo_academico.min'         => 'O motivo académico deve ter pelo menos 5 caracteres.',
            'motivo_academico.max'         => 'O motivo académico não pode exceder 200 caracteres.',
        ];
    }
}
