<?php

namespace App\Services;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\DTOs\Auth\LoginDTO;
use App\Models\Utilizador;

class AuthService
{
    public function __construct(
        private readonly LoginAction  $loginAction,
        private readonly LogoutAction $logoutAction,
    ) {}

    public function login(LoginDTO $dto): array
    {
        return $this->loginAction->execute($dto);
    }

    public function logout(Utilizador $utilizador): void
    {
        $this->logoutAction->execute($utilizador);
    }
}
