<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarCompSaidaAntecipadaAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class CompSaidaAntecipadaService
{
    public function __construct(private readonly CriarCompSaidaAntecipadaAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
