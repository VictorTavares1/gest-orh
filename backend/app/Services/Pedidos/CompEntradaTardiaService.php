<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarCompEntradaTardiaAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class CompEntradaTardiaService
{
    public function __construct(private readonly CriarCompEntradaTardiaAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
