<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarFormacaoAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class FormacaoService
{
    public function __construct(private readonly CriarFormacaoAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
