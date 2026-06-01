<?php

namespace App\Actions\Auth;

use App\Models\Utilizador;

class LogoutAction
{
    public function execute(Utilizador $utilizador): void
    {
        $utilizador->tokens()->delete();
    }
}
