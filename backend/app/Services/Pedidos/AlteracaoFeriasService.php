<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarAlteracaoFeriasAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class AlteracaoFeriasService
{
    public function __construct(private readonly CriarAlteracaoFeriasAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
