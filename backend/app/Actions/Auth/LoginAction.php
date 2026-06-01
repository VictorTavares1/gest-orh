<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginDTO;
use App\Models\Utilizador;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    public function execute(LoginDTO $dto): array
    {
        $utilizador = Utilizador::with(['tipoUtilizador', 'setor.organizacao'])
            ->where('email', $dto->email)
            ->first();

        if (! $utilizador || ! Hash::check($dto->password, $utilizador->password_hash)) {
            abort(401, 'Credenciais inválidas.');
        }

        if (! $utilizador->ativo) {
            abort(403, 'Conta desativada. Contacte o administrador.');
        }

        $utilizador->tokens()->delete();

        $token = $utilizador->createToken('api-token')->plainTextToken;

        return compact('utilizador', 'token');
    }
}
