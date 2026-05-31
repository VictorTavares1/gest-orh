<?php

namespace App\Http\Requests\Utilizador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUtilizadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $utilizador = $this->route('utilizador');

        return [
            'nome'               => ['sometimes', 'string', 'max:255'],
            'email'              => ['sometimes', 'email', Rule::unique('utilizador', 'email')->ignore($utilizador?->getKey(), 'id_utilizador')],
            'password'           => ['sometimes', 'string', 'min:8'],
            'id_setor'           => ['sometimes', 'integer', 'exists:setor,id_setor'],
            'id_tipo_utilizador' => ['sometimes', 'integer', 'exists:tipo_utilizador,id_tipo_utilizador'],
        ];
    }
}
