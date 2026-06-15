<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarOutrosAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class OutrosService
{
    public function __construct(private readonly CriarOutrosAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
