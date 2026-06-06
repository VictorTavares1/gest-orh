<?php

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->user()?->getKey();

        return [
            'nome'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', Rule::unique('utilizador', 'email')->ignore($id, 'id_utilizador')],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}
