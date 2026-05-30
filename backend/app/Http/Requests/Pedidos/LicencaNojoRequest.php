<?php

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

class LicencaNojoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dias_ausencia'   => ['required', 'integer', 'min:1', 'max:30'],
            'nome_falecido'   => ['required', 'string', 'max:150'],
            'grau_parentesco' => ['required', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'dias_ausencia.required'   => 'O número de dias de ausência é obrigatório.',
            'dias_ausencia.integer'    => 'O número de dias deve ser um valor inteiro.',
            'dias_ausencia.min'        => 'O mínimo é 1 dia de ausência.',
            'dias_ausencia.max'        => 'O máximo é 30 dias de ausência.',
            'nome_falecido.required'   => 'O nome do falecido é obrigatório.',
            'nome_falecido.max'        => 'O nome não pode exceder 150 caracteres.',
            'grau_parentesco.required' => 'O grau de parentesco é obrigatório.',
            'grau_parentesco.max'      => 'O grau de parentesco não pode exceder 60 caracteres.',
        ];
    }
}
