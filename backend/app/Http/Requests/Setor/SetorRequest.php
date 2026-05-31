<?php

namespace App\Http\Requests\Setor;

use Illuminate\Foundation\Http\FormRequest;

class SetorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome'           => ['required', 'string', 'max:255'],
            'id_organizacao' => ['required', 'integer', 'exists:organizacao,id_organizacao'],
        ];
    }
}
