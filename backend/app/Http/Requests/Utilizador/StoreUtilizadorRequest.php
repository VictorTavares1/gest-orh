<?php

namespace App\Http\Requests\Utilizador;

use Illuminate\Foundation\Http\FormRequest;

class StoreUtilizadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'email', 'unique:utilizador,email'],
            'password'           => ['required', 'string', 'min:8'],
            'id_setor'           => ['required', 'integer', 'exists:setor,id_setor'],
            'id_tipo_utilizador' => ['required', 'integer', 'exists:tipo_utilizador,id_tipo_utilizador'],
            'ativo'              => ['sometimes', 'boolean'],
        ];
    }
}
