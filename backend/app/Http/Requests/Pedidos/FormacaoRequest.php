<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class FormacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_formacao' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'tema_formacao' => ['required', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_formacao.required'         => 'A data da formação é obrigatória.',
            'data_formacao.date_format'      => 'A data deve estar no formato AAAA-MM-DD.',
            'data_formacao.after_or_equal'   => 'A data da formação deve ser hoje ou uma data futura.',
            'tema_formacao.required'         => 'O tema da formação é obrigatório.',
            'tema_formacao.max'              => 'O tema não pode exceder 200 caracteres.',
        ];
    }
}
