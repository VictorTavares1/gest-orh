<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarFolgaAniversarioAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class FolgaAniversarioService
{
    public function __construct(private readonly CriarFolgaAniversarioAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
