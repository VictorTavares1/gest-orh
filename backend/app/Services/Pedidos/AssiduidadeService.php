<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarAssiduidadeAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class AssiduidadeService
{
    public function __construct(private readonly CriarAssiduidadeAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
