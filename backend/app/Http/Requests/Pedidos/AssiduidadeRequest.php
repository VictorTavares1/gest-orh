<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class AssiduidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_sem_picagem' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'horario'          => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'data_sem_picagem.required'        => 'A data sem picagem é obrigatória.',
            'data_sem_picagem.date_format'     => 'A data deve estar no formato AAAA-MM-DD.',
            'data_sem_picagem.before_or_equal' => 'A data sem picagem não pode ser no futuro.',
            'horario.required'                 => 'O horário de entrada é obrigatório.',
            'horario.date_format'              => 'O horário deve estar no formato HH:MM.',
        ];
    }
}
